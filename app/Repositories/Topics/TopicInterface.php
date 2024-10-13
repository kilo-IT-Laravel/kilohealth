<?php

namespace App\Repositories\Topics;

use App\Models\topic;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TopicInterface {
    public function getAllTopics(string $search = null , int $perPage = 10): LengthAwarePaginator;
    public function getTopicById(int $id): ? topic;
    public function createTopic(array $topicDetails): topic;
    public function updateTopic(int $id, array $newDetails): bool;
    public function getTopicsByCategory(int $categoryId): Collection;

    public function deleteTopic(int $id): bool;
    public function restoreTopic(int $id): bool;
    public function forceDeleteTopic(int $id): bool;
    public function getTrashedTopics(string $search = null, int $perPage = 10): LengthAwarePaginator;
}
