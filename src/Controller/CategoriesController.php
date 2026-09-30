<?php
declare(strict_types=1);

namespace App\Controller;

use App\Exception\ApiException;
use App\Utility\Validator;
use Cake\Datasource\EntityInterface;

class CategoriesController extends AppController
{
    public function index(): \Cake\Http\Response
    {
        $skip = (int)$this->request->getQuery('skip', 0);
        $limit = (int)$this->request->getQuery('limit', 10);
        $error = Validator::firstError([
            'skip' => $skip,
            'limit' => $limit,
        ], [
            'skip' => ['integer', 'min:0'],
            'limit' => ['integer', 'min:1', 'max:100'],
        ]);
        if ($error !== null) {
            throw new ApiException($error, 422);
        }

        $table = $this->fetchTable('Categories');
        $query = $table->find()->orderBy(['Categories.id' => 'ASC']);
        $total = $query->count();
        $rows = $query->limit($limit)->offset($skip)->all();

        return $this->json([
            'items' => array_map(fn (EntityInterface $category) => $this->categoryJson($category), iterator_to_array($rows)),
            'total' => $total,
            'skip' => $skip,
            'limit' => $limit,
        ]);
    }

    public function view(string $id): \Cake\Http\Response
    {
        $category = $this->fetchTable('Categories')->getOrFailApi((int)$id);

        return $this->json($this->categoryJson($category));
    }

    public function add(): \Cake\Http\Response
    {
        $payload = $this->payload();
        $error = Validator::firstError($payload, [
            'name' => ['required', 'string', 'min:1', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);
        if ($error !== null) {
            throw new ApiException($error, 422);
        }

        $category = $this->fetchTable('Categories')->createFromApi($payload);

        return $this->json($this->categoryJson($category), 201);
    }

    public function edit(string $id): \Cake\Http\Response
    {
        $payload = $this->payload();
        $category = $this->fetchTable('Categories')->updateFromApi((int)$id, $payload);

        return $this->json($this->categoryJson($category));
    }

    public function delete(string $id): \Cake\Http\Response
    {
        $this->fetchTable('Categories')->deleteFromApi((int)$id);

        return $this->response->withStatus(204);
    }

    /**
     * @return array<string, mixed>
     */
    private function categoryJson(EntityInterface $category): array
    {
        return [
            'id' => (int)$category->id,
            'name' => $category->name,
            'description' => $category->description,
        ];
    }
}
