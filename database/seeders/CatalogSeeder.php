<?php

namespace Database\Seeders;

use App\Actions\Questions\SaveQuestion;
use App\Enums\QuestionDifficulty;
use App\Enums\QuestionSource;
use App\Enums\QuestionType;
use App\Enums\ReviewStatus;
use App\Enums\TermEventType;
use App\Models\AcademicTerm;
use App\Models\Institution;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Database\Seeder;

/**
 * Catálogo de Ciência da Computação: o que os admins cadastrariam pelo
 * painel (instituição, período com calendário, matérias e banco de
 * questões). Idempotente: pode rodar de novo sem duplicar nada.
 */
class CatalogSeeder extends Seeder
{
    public const INSTITUTION_SLUG = 'universidade-federal-exemplo';

    /**
     * code => [nome, carga horária, créditos, cor, descrição]
     *
     * @var array<string, array{0: string, 1: int, 2: int, 3: string, 4: string}>
     */
    private const SUBJECTS = [
        'INF101' => ['Algoritmos e Programação', 72, 4, '#2196f3', 'Lógica de programação, estruturas de controle, funções e recursão.'],
        'INF102' => ['Estruturas de Dados', 72, 4, '#2b5bb5', 'Listas, pilhas, filas, árvores, tabelas hash e grafos, com análise de complexidade.'],
        'INF201' => ['Banco de Dados', 72, 4, '#21638d', 'Modelagem relacional, normalização, SQL e transações.'],
        'INF202' => ['Programação Orientada a Objetos', 72, 4, '#5d98c4', 'Classes, herança, polimorfismo, interfaces e princípios SOLID.'],
        'INF301' => ['Engenharia de Software', 54, 3, '#0061a4', 'Processos de desenvolvimento, requisitos, testes e qualidade de software.'],
        'INF302' => ['Sistemas Operacionais', 72, 4, '#00497d', 'Processos, threads, escalonamento, memória e sistemas de arquivos.'],
        'INF303' => ['Redes de Computadores', 72, 4, '#759efd', 'Modelo TCP/IP, roteamento, protocolos de transporte e aplicação.'],
        'MAT101' => ['Cálculo I', 90, 5, '#93cdfc', 'Limites, derivadas e integrais de funções de uma variável.'],
        'MAT201' => ['Matemática Discreta', 54, 3, '#9ecaff', 'Lógica, conjuntos, indução, combinatória e relações.'],
    ];

    /**
     * Matérias do catálogo geral (sem instituição).
     *
     * @var array<string, array{0: string, 1: int, 2: int, 3: string, 4: string}>
     */
    private const GENERAL_SUBJECTS = [
        'ING001' => ['Inglês Instrumental', 36, 2, '#b0c6ff', 'Leitura de textos técnicos de computação em inglês.'],
    ];

    public function run(SaveQuestion $saveQuestion): void
    {
        $institution = Institution::firstOrCreate(['slug' => self::INSTITUTION_SLUG], [
            'name' => 'Universidade Federal Exemplo',
            'document' => '00.000.000/0001-00',
            'total_points' => 100,
            'passing_percent' => 60,
            'max_absence_percent' => 25,
        ]);

        $this->seedTerm($institution);

        $subjects = collect(self::SUBJECTS)
            ->map(fn (array $data, string $code) => $this->subject($code, $data, $institution))
            ->merge(collect(self::GENERAL_SUBJECTS)->map(fn (array $data, string $code) => $this->subject($code, $data, null)));

        foreach ($this->questions() as $code => $questions) {
            foreach ($questions as $question) {
                $this->seedQuestion($saveQuestion, $subjects[$code], $question);
            }
        }
    }

    /**
     * Período atual (semestre corrente), com o calendário de provas.
     */
    public static function currentTermName(): string
    {
        return today()->format('Y').'.'.(today()->month <= 6 ? 1 : 2);
    }

    private function seedTerm(Institution $institution): AcademicTerm
    {
        $semesterStart = today()->month <= 6 ? today()->startOfYear()->addMonths(2) : today()->startOfYear()->addMonths(7);

        $term = AcademicTerm::firstOrCreate(
            ['institution_id' => $institution->id, 'name' => self::currentTermName()],
            [
                'starts_on' => $semesterStart->copy()->startOfMonth()->toDateString(),
                'ends_on' => $semesterStart->copy()->addMonths(4)->endOfMonth()->toDateString(),
            ],
        );

        if ($term->events()->doesntExist()) {
            $term->events()->createMany([
                ['title' => 'Semana de provas P1', 'type' => TermEventType::ExamWeek, 'starts_on' => today()->addWeeks(2)->startOfWeek(), 'ends_on' => today()->addWeeks(2)->startOfWeek()->addDays(4)],
                ['title' => 'Recesso acadêmico', 'type' => TermEventType::Recess, 'starts_on' => today()->addWeeks(4)->startOfWeek(), 'ends_on' => today()->addWeeks(4)->startOfWeek()->addDays(4)],
                ['title' => 'Semana de provas P2', 'type' => TermEventType::ExamWeek, 'starts_on' => today()->addWeeks(8)->startOfWeek(), 'ends_on' => today()->addWeeks(8)->startOfWeek()->addDays(4)],
            ]);
        }

        return $term;
    }

    /**
     * @param  array{0: string, 1: int, 2: int, 3: string, 4: string}  $data
     */
    private function subject(string $code, array $data, ?Institution $institution): Subject
    {
        [$name, $workload, $credits, $color, $description] = $data;

        return Subject::firstOrCreate(
            ['institution_id' => $institution?->id, 'code' => $code],
            ['name' => $name, 'workload_hours' => $workload, 'credits' => $credits, 'color' => $color, 'description' => $description],
        );
    }

    /**
     * @param  array{type: QuestionType, difficulty: QuestionDifficulty, topic: string, statement: string, options?: array<int, string>, answer: int|bool|string, explanation: string}  $question
     */
    private function seedQuestion(SaveQuestion $saveQuestion, Subject $subject, array $question): void
    {
        if (Question::withTrashed()->where('statement_hash', Question::hashStatement($question['statement']))->exists()) {
            return;
        }

        $options = match ($question['type']) {
            QuestionType::MultipleChoice => collect($question['options'])
                ->map(fn (string $content, int $index): array => ['content' => $content, 'is_correct' => $index === $question['answer']])
                ->all(),
            QuestionType::TrueFalse => [
                ['content' => __('Verdadeiro'), 'is_correct' => $question['answer'] === true],
                ['content' => __('Falso'), 'is_correct' => $question['answer'] === false],
            ],
            QuestionType::Numeric => [['content' => (string) $question['answer'], 'is_correct' => true]],
            QuestionType::Open => [],
        };

        $saveQuestion->handle([
            'subject_id' => $subject->id,
            'type' => $question['type'],
            'difficulty' => $question['difficulty'],
            'topic' => $question['topic'],
            'statement' => $question['statement'],
            'explanation' => $question['explanation'],
            'source' => QuestionSource::Manual,
            'review_status' => ReviewStatus::Approved,
        ], $options);
    }

    /**
     * Banco de questões por código de matéria.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function questions(): array
    {
        $mc = QuestionType::MultipleChoice;
        $tf = QuestionType::TrueFalse;
        $num = QuestionType::Numeric;
        $easy = QuestionDifficulty::Easy;
        $medium = QuestionDifficulty::Medium;
        $hard = QuestionDifficulty::Hard;

        return [
            'INF101' => [
                ['type' => $mc, 'difficulty' => $easy, 'topic' => 'Estruturas de repetição', 'statement' => 'Quantas vezes o corpo do laço "for (i = 0; i < 10; i += 2)" é executado?', 'options' => ['4', '5', '6', '10'], 'answer' => 1, 'explanation' => 'i assume 0, 2, 4, 6 e 8: cinco execuções.'],
                ['type' => $tf, 'difficulty' => $easy, 'topic' => 'Recursão', 'statement' => 'Toda função recursiva precisa de pelo menos um caso base para terminar.', 'answer' => true, 'explanation' => 'Sem caso base a recursão nunca para e estoura a pilha de chamadas.'],
                ['type' => $num, 'difficulty' => $medium, 'topic' => 'Recursão', 'statement' => 'Qual é o valor de fatorial(5), sendo fatorial(n) = n × fatorial(n − 1) e fatorial(0) = 1?', 'answer' => 120, 'explanation' => '5 × 4 × 3 × 2 × 1 = 120.'],
                ['type' => $mc, 'difficulty' => $medium, 'topic' => 'Tipos de dados', 'statement' => 'Em C, qual é o resultado da expressão inteira 7 / 2?', 'options' => ['3', '3.5', '4', 'Erro de compilação'], 'answer' => 0, 'explanation' => 'Divisão entre inteiros descarta a parte fracionária.'],
            ],
            'INF102' => [
                ['type' => $mc, 'difficulty' => $easy, 'topic' => 'Busca', 'statement' => 'Qual a complexidade da busca binária em um vetor ordenado de n elementos?', 'options' => ['O(1)', 'O(log n)', 'O(n)', 'O(n log n)'], 'answer' => 1, 'explanation' => 'A cada comparação o espaço de busca cai pela metade.'],
                ['type' => $mc, 'difficulty' => $easy, 'topic' => 'Pilhas e filas', 'statement' => 'Qual estrutura segue a política LIFO (último a entrar, primeiro a sair)?', 'options' => ['Fila', 'Pilha', 'Lista duplamente encadeada', 'Heap'], 'answer' => 1, 'explanation' => 'Na pilha, push e pop acontecem no mesmo extremo (topo).'],
                ['type' => $tf, 'difficulty' => $medium, 'topic' => 'Árvores', 'statement' => 'Em uma árvore AVL, a diferença de altura entre as subárvores de qualquer nó é no máximo 1.', 'answer' => true, 'explanation' => 'Essa é a condição de balanceamento AVL (fator de balanceamento entre -1 e 1).'],
                ['type' => $mc, 'difficulty' => $hard, 'topic' => 'Ordenação', 'statement' => 'Qual algoritmo tem complexidade O(n log n) no pior caso?', 'options' => ['Quicksort', 'Insertion sort', 'Merge sort', 'Bubble sort'], 'answer' => 2, 'explanation' => 'O merge sort sempre divide ao meio; o quicksort pode cair em O(n²) com pivôs ruins.'],
            ],
            'INF201' => [
                ['type' => $mc, 'difficulty' => $easy, 'topic' => 'SQL', 'statement' => 'Qual cláusula SQL filtra o resultado de uma agregação feita com GROUP BY?', 'options' => ['WHERE', 'HAVING', 'ORDER BY', 'LIMIT'], 'answer' => 1, 'explanation' => 'WHERE filtra linhas antes do agrupamento; HAVING filtra os grupos.'],
                ['type' => $mc, 'difficulty' => $medium, 'topic' => 'Normalização', 'statement' => 'Uma tabela está na 2FN quando está na 1FN e…', 'options' => ['não possui chave primária composta', 'nenhum atributo não-chave depende parcialmente da chave', 'não há dependências transitivas', 'todos os atributos são numéricos'], 'answer' => 1, 'explanation' => 'Dependência transitiva é tratada na 3FN; a 2FN elimina dependências parciais.'],
                ['type' => $tf, 'difficulty' => $medium, 'topic' => 'Transações', 'statement' => 'O "I" de ACID significa Integridade.', 'answer' => false, 'explanation' => 'O "I" é de Isolamento (Isolation): transações concorrentes não interferem entre si.'],
            ],
            'INF202' => [
                ['type' => $mc, 'difficulty' => $easy, 'topic' => 'Encapsulamento', 'statement' => 'Qual modificador de acesso restringe um atributo à própria classe?', 'options' => ['public', 'protected', 'private', 'static'], 'answer' => 2, 'explanation' => 'private só é acessível dentro da classe que o declara.'],
                ['type' => $mc, 'difficulty' => $medium, 'topic' => 'SOLID', 'statement' => 'Qual princípio SOLID diz que uma classe deve ter apenas um motivo para mudar?', 'options' => ['Aberto/Fechado', 'Responsabilidade Única', 'Substituição de Liskov', 'Inversão de Dependência'], 'answer' => 1, 'explanation' => 'Single Responsibility Principle: uma responsabilidade, um motivo para mudar.'],
                ['type' => $tf, 'difficulty' => $medium, 'topic' => 'Polimorfismo', 'statement' => 'Sobrescrita de método (override) é uma forma de polimorfismo em tempo de execução.', 'answer' => true, 'explanation' => 'O método chamado é decidido pelo tipo real do objeto em tempo de execução.'],
            ],
            'INF301' => [
                ['type' => $mc, 'difficulty' => $easy, 'topic' => 'Metodologias ágeis', 'statement' => 'No Scrum, quem é responsável por priorizar o Product Backlog?', 'options' => ['Scrum Master', 'Product Owner', 'Time de desenvolvimento', 'Cliente final'], 'answer' => 1, 'explanation' => 'O Product Owner maximiza o valor do produto ordenando o backlog.'],
                ['type' => $tf, 'difficulty' => $easy, 'topic' => 'Testes', 'statement' => 'Testes unitários verificam a integração entre vários módulos do sistema.', 'answer' => false, 'explanation' => 'Isso é teste de integração; o unitário verifica uma unidade isolada.'],
                ['type' => $mc, 'difficulty' => $medium, 'topic' => 'Requisitos', 'statement' => '"O sistema deve responder em menos de 2 segundos" é um requisito…', 'options' => ['funcional', 'não funcional', 'de domínio', 'de usuário'], 'answer' => 1, 'explanation' => 'Desempenho é uma qualidade do sistema, não uma função que ele executa.'],
            ],
            'INF302' => [
                ['type' => $mc, 'difficulty' => $medium, 'topic' => 'Deadlock', 'statement' => 'Qual NÃO é uma das condições de Coffman para ocorrer deadlock?', 'options' => ['Exclusão mútua', 'Posse e espera', 'Preempção de recursos', 'Espera circular'], 'answer' => 2, 'explanation' => 'A condição é a NÃO preempção; com preempção o deadlock pode ser quebrado.'],
                ['type' => $tf, 'difficulty' => $easy, 'topic' => 'Processos e threads', 'statement' => 'Threads de um mesmo processo compartilham o mesmo espaço de endereçamento.', 'answer' => true, 'explanation' => 'Cada thread tem sua pilha e registradores, mas a memória do processo é comum.'],
                ['type' => $mc, 'difficulty' => $medium, 'topic' => 'Escalonamento', 'statement' => 'Qual algoritmo de escalonamento usa um quantum de tempo fixo por processo?', 'options' => ['FIFO', 'SJF', 'Round Robin', 'Prioridade não preemptiva'], 'answer' => 2, 'explanation' => 'No Round Robin cada processo executa até o fim do quantum e volta para a fila.'],
            ],
            'INF303' => [
                ['type' => $mc, 'difficulty' => $easy, 'topic' => 'Camada de transporte', 'statement' => 'Qual protocolo de transporte garante entrega ordenada e confiável?', 'options' => ['UDP', 'TCP', 'IP', 'ICMP'], 'answer' => 1, 'explanation' => 'O TCP é orientado a conexão, com confirmação e retransmissão.'],
                ['type' => $num, 'difficulty' => $medium, 'topic' => 'Endereçamento IP', 'statement' => 'Quantos endereços de host utilizáveis existem em uma rede /24?', 'answer' => 254, 'explanation' => '2⁸ = 256 endereços, menos o de rede e o de broadcast.'],
                ['type' => $mc, 'difficulty' => $easy, 'topic' => 'Camada de aplicação', 'statement' => 'Qual porta é usada por padrão pelo HTTPS?', 'options' => ['80', '21', '443', '8080'], 'answer' => 2, 'explanation' => 'HTTP usa a 80 e HTTPS a 443.'],
            ],
            'MAT101' => [
                ['type' => $mc, 'difficulty' => $easy, 'topic' => 'Derivadas', 'statement' => 'Qual é a derivada de f(x) = x²?', 'options' => ['x', '2x', 'x²', '2'], 'answer' => 1, 'explanation' => 'Pela regra da potência, d/dx xⁿ = n·xⁿ⁻¹.'],
                ['type' => $tf, 'difficulty' => $medium, 'topic' => 'Continuidade', 'statement' => 'Toda função contínua é derivável.', 'answer' => false, 'explanation' => 'f(x) = |x| é contínua em 0, mas não é derivável nesse ponto.'],
                ['type' => $num, 'difficulty' => $medium, 'topic' => 'Integrais', 'statement' => 'Quanto vale a integral de 0 a 2 de 3x² dx?', 'answer' => 8, 'explanation' => 'A primitiva é x³; 2³ − 0³ = 8.'],
            ],
            'MAT201' => [
                ['type' => $num, 'difficulty' => $easy, 'topic' => 'Combinatória', 'statement' => 'Quantos subconjuntos tem um conjunto com 5 elementos?', 'answer' => 32, 'explanation' => 'Um conjunto com n elementos tem 2ⁿ subconjuntos: 2⁵ = 32.'],
                ['type' => $mc, 'difficulty' => $medium, 'topic' => 'Lógica', 'statement' => 'Qual é a negação de "todo aluno estudou"?', 'options' => ['Nenhum aluno estudou', 'Algum aluno não estudou', 'Todo aluno não estudou', 'Algum aluno estudou'], 'answer' => 1, 'explanation' => 'A negação de ∀x P(x) é ∃x ¬P(x).'],
            ],
            'ING001' => [
                ['type' => $mc, 'difficulty' => $easy, 'topic' => 'Vocabulário técnico', 'statement' => 'Em documentação técnica, "deprecated" indica que um recurso…', 'options' => ['é novo', 'será removido no futuro e não deve ser usado', 'está em testes', 'é obrigatório'], 'answer' => 1, 'explanation' => 'Deprecated = obsoleto: ainda funciona, mas tende a ser removido.'],
            ],
        ];
    }
}
