<?php

namespace App\Notifications\Application;

use App\Models\SavedPartSearch;
use Illuminate\Notifications\Notification;

class SavedPartSearchMatchedNotification extends Notification
{
    public function __construct(private SavedPartSearch $savedPartSearch)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $savedPartSearch = $this->savedPartSearch->loadMissing('matchedPart');
        $vehicleLabel = trim($savedPartSearch->vehicle_brand . ' ' . $savedPartSearch->vehicle_model);

        if ($savedPartSearch->vehicle_year) {
            $vehicleLabel .= ' ' . $savedPartSearch->vehicle_year;
        }

        return [
            'kind' => 'saved_part_search_matched',
            'title' => 'Correspondance trouvée',
            'message' => 'Une pièce correspondant à votre recherche ' . $vehicleLabel . ' — ' . $savedPartSearch->part_name . ' est maintenant disponible.',
            'url' => $savedPartSearch->matchedPart
                ? route('pieces.show', $savedPartSearch->matchedPart)
                : route('client.saved-searches.index'),
            'saved_part_search_id' => $savedPartSearch->id,
            'matched_part_id' => $savedPartSearch->matched_part_id,
        ];
    }
}
