<?php

namespace App\Support\Locations;

use App\Models\User;
use App\Models\UserLocation;
use Illuminate\Support\Collection;

/**
 * Estudantes cuja posição mais recente está perto da posição mais recente de
 * quem pergunta. Só a distância aproximada sai daqui: coordenadas de outras
 * pessoas nunca chegam à tela.
 *
 * O retângulo (latitude/longitude) corta a busca no banco de forma portátil;
 * a distância real (haversine) é calculada em PHP só para quem sobrou.
 */
class NearbyStudents
{
    public const RADIUS_KM = 10;

    /** Posições mais antigas que isso não contam: a pessoa pode ter ido embora. */
    public const RECENT_DAYS = 7;

    public const LIMIT = 20;

    private const EARTH_RADIUS_KM = 6371.0;

    private const KM_PER_DEGREE = 111.32;

    /**
     * NULL quando o próprio estudante ainda não tem posição recente.
     *
     * @return Collection<int, array{user: User, distance_km: float}>|null
     */
    public function for(User $user): ?Collection
    {
        $origin = $this->latestPositionOf($user);

        if ($origin === null) {
            return null;
        }

        $latitudeDelta = self::RADIUS_KM / self::KM_PER_DEGREE;
        $longitudeDelta = self::RADIUS_KM / (self::KM_PER_DEGREE * max(cos(deg2rad($origin->latitude)), 0.01));

        return UserLocation::query()
            ->whereIn('id', UserLocation::query()
                ->selectRaw('max(id)')
                ->where('user_id', '!=', $user->id)
                ->where('recorded_at', '>=', now()->subDays(self::RECENT_DAYS))
                ->groupBy('user_id'))
            ->whereBetween('latitude', [$origin->latitude - $latitudeDelta, $origin->latitude + $latitudeDelta])
            ->whereBetween('longitude', [$origin->longitude - $longitudeDelta, $origin->longitude + $longitudeDelta])
            ->with('user')
            ->get()
            ->map(fn (UserLocation $location): array => [
                'user' => $location->user,
                'distance_km' => $this->distanceInKm($origin, $location),
            ])
            ->filter(fn (array $nearby): bool => $nearby['distance_km'] <= self::RADIUS_KM)
            ->sortBy('distance_km')
            ->take(self::LIMIT)
            ->values();
    }

    public function includes(User $user, int $otherUserId): bool
    {
        return (bool) $this->for($user)?->contains(fn (array $nearby): bool => $nearby['user']->id === $otherUserId);
    }

    private function latestPositionOf(User $user): ?UserLocation
    {
        return $user->locations()
            ->where('recorded_at', '>=', now()->subDays(self::RECENT_DAYS))
            ->latest('recorded_at')
            ->latest('id')
            ->first();
    }

    private function distanceInKm(UserLocation $from, UserLocation $to): float
    {
        $latitudeDelta = deg2rad($to->latitude - $from->latitude);
        $longitudeDelta = deg2rad($to->longitude - $from->longitude);

        $haversine = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($from->latitude)) * cos(deg2rad($to->latitude)) * sin($longitudeDelta / 2) ** 2;

        return 2 * self::EARTH_RADIUS_KM * asin(min(1.0, sqrt($haversine)));
    }
}
