/**
 * UniFocus Student Platform - Mobile App Interactive JavaScript
 * Pure Vanilla ES6+ (Mobile-First / PWA & Capacitor Ready)
 * Projeto Interdisciplinar - 3º Semestre Ciência da Computação
 */

document.addEventListener('DOMContentLoaded', () => {
  initServiceWorker();
  initTheme();
  initFlashcard();
  initDailyChallenges();
  initStreaks();
  initNavigation();
});

/* --- 0. Service Worker Registration (PWA / Offline Support) --- */
function initServiceWorker() {
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('./sw.js')
        .then((reg) => console.log('UniFocus Service Worker registrado:', reg.scope))
        .catch((err) => console.log('Falha ao registrar Service Worker:', err));
    });
  }
}

/* --- Haptic Feedback Helper (Vibração nativa em celulares) --- */
function triggerHaptic(type = 'light') {
  if ('vibrate' in navigator) {
    if (type === 'light') navigator.vibrate(10);
    else if (type === 'medium') navigator.vibrate(25);
    else if (type === 'success') navigator.vibrate([15, 50, 25]);
  }
}

/* --- 1. Theme Management (Dark / Light Mode com persistência) --- */
function initTheme() {
  const themeToggleBtn = document.getElementById('themeToggleBtn');
  const savedTheme = localStorage.getItem('unifocus_theme') || 'light';
  
  applyTheme(savedTheme);

  if (themeToggleBtn) {
    themeToggleBtn.addEventListener('click', () => {
      triggerHaptic('light');
      const currentTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
      const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
      applyTheme(newTheme);
      showToast(`Tema ${newTheme === 'dark' ? 'Escuro' : 'Claro'} ativado!`, 'info');
    });
  }
}

function applyTheme(theme) {
  if (theme === 'dark') {
    document.documentElement.setAttribute('data-theme', 'dark');
  } else {
    document.documentElement.removeAttribute('data-theme');
  }
  localStorage.setItem('unifocus_theme', theme);
  
  const themeToggleIcon = document.getElementById('themeToggleIcon');
  if (themeToggleIcon) {
    themeToggleIcon.textContent = theme === 'dark' ? 'light_mode' : 'dark_mode';
  }
}

/* --- 2. Interactive 3D Flashcard --- */
const flashcardsDeck = [
  {
    topic: 'Estruturas de Dados',
    tag: 'Flash Card • Revisão Diária',
    question: 'Conceitos fundamentais de Árvores Binárias de Busca e balanceamento AVL. Toque para virar.',
    answer: 'Árvore AVL é uma árvore de busca binária auto-balanceada onde a diferença de altura entre subárvores esquerda e direita não passa de 1 para qualquer nó (Fator de Balanceamento ∈ {-1, 0, 1}).'
  },
  {
    topic: 'Cálculo I',
    tag: 'Flash Card • Limites & Derivadas',
    question: 'Qual é a definição da Regra da Cadeia para diferenciação de funções compostas?',
    answer: 'Se f e g são deriváveis, a derivada da função composta (f ∘ g)(x) é dada por: (f ∘ g)\'(x) = f\'(g(x)) · g\'(x).'
  },
  {
    topic: 'Banco de Dados',
    tag: 'Flash Card • Modelagem Relacional',
    question: 'Qual a diferença entre a Primeira (1FN) e a Segunda Forma Normal (2FN)?',
    answer: 'Na 1FN todos os atributos são atômicos. Na 2FN, além de estar na 1FN, nenhum atributo não-chave depende parcialmente de uma chave candidata composta.'
  }
];

let currentCardIndex = 0;

function initFlashcard() {
  const flashcardContainer = document.getElementById('dailyFlashcard');
  const startSessionBtn = document.getElementById('startSessionBtn');
  const nextCardBtn = document.getElementById('nextCardBtn');

  if (flashcardContainer) {
    flashcardContainer.addEventListener('click', (e) => {
      if (e.target.closest('.action-btn-click')) return;
      triggerHaptic('light');
      flashcardContainer.classList.toggle('is-flipped');
    });
  }

  if (startSessionBtn) {
    startSessionBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      triggerHaptic('medium');
      flashcardContainer.classList.add('is-flipped');
      showToast('Sessão iniciada! Resposta exibida.', 'info');
    });
  }

  if (nextCardBtn) {
    nextCardBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      triggerHaptic('light');
      flashcardContainer.classList.remove('is-flipped');
      setTimeout(() => {
        currentCardIndex = (currentCardIndex + 1) % flashcardsDeck.length;
        renderCard(currentCardIndex);
        showToast(`Novo card carregado: ${flashcardsDeck[currentCardIndex].topic}`, 'info');
      }, 300);
    });
  }
}

function renderCard(index) {
  const card = flashcardsDeck[index];
  const tagEl = document.getElementById('cardTagFront');
  const titleEl = document.getElementById('cardTitleFront');
  const descEl = document.getElementById('cardDescFront');
  const titleBack = document.getElementById('cardTitleBack');
  const descBack = document.getElementById('cardDescBack');

  if (tagEl) tagEl.textContent = card.tag;
  if (titleEl) titleEl.textContent = card.topic;
  if (descEl) descEl.textContent = card.question;
  if (titleBack) titleBack.textContent = card.topic;
  if (descBack) descBack.textContent = card.answer;
}

/* --- 3. Daily Challenges Progress Tracker (com LocalStorage) --- */
function initDailyChallenges() {
  const checkboxes = document.querySelectorAll('.challenge-checkbox');
  const counterBadge = document.getElementById('challengesBadge');
  const progressBar = document.getElementById('sidebarProgressFill');
  const progressText = document.getElementById('sidebarProgressText');

  // Recuperar estado salvo do localStorage
  const savedState = JSON.parse(localStorage.getItem('unifocus_challenges') || '[]');
  checkboxes.forEach((cb, idx) => {
    if (savedState[idx]) {
      cb.checked = true;
      cb.closest('.challenge-item')?.classList.add('completed');
    }
  });

  function updateChallengeProgress() {
    let completedCount = 0;
    const currentState = [];

    checkboxes.forEach((cb) => {
      currentState.push(cb.checked);
      const parentItem = cb.closest('.challenge-item');
      if (cb.checked) {
        completedCount++;
        if (parentItem) parentItem.classList.add('completed');
      } else {
        if (parentItem) parentItem.classList.remove('completed');
      }
    });

    localStorage.setItem('unifocus_challenges', JSON.stringify(currentState));

    const total = checkboxes.length;
    if (counterBadge) {
      counterBadge.textContent = `${completedCount}/${total} Completos`;
      if (completedCount === total) {
        counterBadge.style.background = '#10b981';
      } else {
        counterBadge.style.background = '';
      }
    }

    const percentage = Math.round((completedCount / total) * 100);
    if (progressBar) {
      progressBar.style.width = `${percentage}%`;
    }
    if (progressText) {
      progressText.textContent = `${percentage}% da meta diária`;
    }

    if (completedCount === total) {
      triggerHaptic('success');
      showToast('🎉 Parabéns! Todos os desafios diários foram concluídos!', 'success');
    } else {
      triggerHaptic('light');
    }
  }

  checkboxes.forEach((cb) => {
    cb.addEventListener('change', updateChallengeProgress);
  });

  updateChallengeProgress();
}

/* --- 4. Streaks Interaction --- */
function initStreaks() {
  const streakBadge = document.getElementById('streakBadge');
  if (streakBadge) {
    streakBadge.addEventListener('click', () => {
      triggerHaptic('medium');
      showToast('🔥 Sequência ativa de 15 dias de estudo contínuo!', 'info');
    });
  }

  const friendAvatars = document.querySelectorAll('.friend-avatar');
  friendAvatars.forEach((avatar, idx) => {
    avatar.addEventListener('click', () => {
      triggerHaptic('light');
      const names = ['Mariana Costa', 'Lucas Silva'];
      showToast(`Colega de turma: ${names[idx] || 'Amigo de estudo'}`, 'info');
    });
  });
}

/* --- 5. Mobile Navigation & UI Feedback --- */
function initNavigation() {
  const navLinks = document.querySelectorAll('.nav-link, .bottom-nav-link');
  navLinks.forEach((link) => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      triggerHaptic('light');
      navLinks.forEach(l => l.classList.remove('active'));
      link.classList.add('active');
      const label = link.querySelector('span:not(.material-symbols-outlined)')?.textContent || 'Aba';
      showToast(`Acessando: ${label}`, 'info');
    });
  });

  const gradeItems = document.querySelectorAll('.grade-item');
  gradeItems.forEach(item => {
    item.addEventListener('click', () => {
      triggerHaptic('light');
      const subject = item.querySelector('.subject-name')?.textContent;
      const grade = item.querySelector('.grade-badge')?.textContent;
      showToast(`Disciplina: ${subject} • Média Atual: ${grade}`, 'info');
    });
  });
}

/* --- Toast Notification Helper --- */
function showToast(message, type = 'info') {
  let container = document.getElementById('toastContainer');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `toast ${type === 'success' ? 'toast-success' : ''}`;
  
  const iconName = type === 'success' ? 'check_circle' : 'info';
  toast.innerHTML = `
    <span class="material-symbols-outlined ${type === 'success' ? 'filled text-green-500' : ''}">${iconName}</span>
    <span>${message}</span>
  `;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(10px) scale(0.95)';
    setTimeout(() => toast.remove(), 300);
  }, 3200);
}
