<?php
declare(strict_types=1);

namespace App\Controller;

use App\Exception\ApiException;
use App\Utility\Validator;
use Cake\Datasource\EntityInterface;

class ItemsController extends AppController
{
    public function index(): \Cake\Http\Response
    {
        $query = $this->request->getQueryParams();
        $skip = (int)($query['skip'] ?? 0);
        $limit = (int)($query['limit'] ?? 10);
        $validationData = [
            'skip' => $skip,
            'limit' => $limit,
            'min_price' => $query['min_price'] ?? null,
            'max_price' => $query['max_price'] ?? null,
            'category_id' => $query['category_id'] ?? null,
            'name_contains' => $query['name_contains'] ?? null,
        ];
        $error = Validator::firstError($validationData, [
            'skip' => ['integer', 'min:0'],
            'limit' => ['integer', 'min:1', 'max:100'],
            'min_price' => ['nullable', 'numeric', 'gt:0'],
            'max_price' => ['nullable', 'numeric', 'gt:0'],
            'category_id' => ['nullable', 'integer', 'min:1'],
            'name_contains' => ['nullable', 'string', 'min:1', 'max:255'],
        ]);
        if ($error !== null) {
            throw new ApiException($error, 422);
        }

        $filters = [];
        foreach (['min_price', 'max_price', 'name_contains'] as $key) {
            if (array_key_exists($key, $query)) {
                $filters[$key] = $query[$key];
            }
        }
        if (array_key_exists('category_id', $query)) {
            $filters['category_id'] = (int)$query['category_id'];
        }

        $table = $this->fetchTable('Items');
        $list = $table->listFiltered($filters);
        $total = $list->count();
        $rows = $list->limit($limit)->offset($skip)->all();

        return $this->json([
            'items' => array_map(fn (EntityInterface $item) => $this->itemJson($item), iterator_to_array($rows)),
            'total' => $total,
            'skip' => $skip,
            'limit' => $limit,
        ]);
    }

    public function view(string $id): \Cake\Http\Response
    {
        $item = $this->fetchTable('Items')->getOrFailApi((int)$id);

        return $this->json($this->itemJson($item));
    }

    public function add(): \Cake\Http\Response
    {
        $payload = $this->payload();
        $error = Validator::firstError($payload, [
            'name' => ['required', 'string', 'min:1', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'gt:0'],
            'category_id' => ['nullable', 'integer', 'min:1'],
        ]);
        if ($error !== null) {
            throw new ApiException($error, 422);
        }

        $payload['price'] = number_format((float)$payload['price'], 2, '.', '');
        if (!isset($payload['category_id'])) {
            $payload['category_id'] = null;
        }
        $item = $this->fetchTable('Items')->createFromApi($payload);

        return $this->json($this->itemJson($item), 201);
    }

    public function edit(string $id): \Cake\Http\Response
    {
        $payload = $this->payload();
        if (array_key_exists('price', $payload) && $payload['price'] !== null) {
            $payload['price'] = number_format((float)$payload['price'], 2, '.', '');
        }
        $item = $this->fetchTable('Items')->updateFromApi((int)$id, $payload);

        return $this->json($this->itemJson($item));
    }

    public function delete(string $id): \Cake\Http\Response
    {
        $this->fetchTable('Items')->deleteFromApi((int)$id);

        return $this->response->withStatus(204);
    }

    public function stats(): \Cake\Http\Response
    {
        return $this->json($this->fetchTable('Items')->stats());
    }

    /**
     * @return array<string, mixed>
     */
    private function itemJson(EntityInterface $item): array
    {
        $category = $item->category ?? null;

        return [
            'id' => (int)$item->id,
            'name' => $item->name,
            'description' => $item->description,
            'price' => (float)$item->price,
            'category_id' => $item->category_id !== null ? (int)$item->category_id : null,
            'category' => $category === null ? null : [
                'id' => (int)$category->id,
                'name' => $category->name,
                'description' => $category->description,
            ],
        ];
    }
}
