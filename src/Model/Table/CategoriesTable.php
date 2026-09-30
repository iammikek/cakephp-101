<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Exception\ApiException;
use Cake\ORM\Table;

class CategoriesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('categories');
        $this->setPrimaryKey('id');
        $this->hasMany('Items', [
            'foreignKey' => 'category_id',
        ]);
    }

    public function getOrFailApi(int $id): \Cake\Datasource\EntityInterface
    {
        $category = $this->find()->where(['Categories.id' => $id])->first();
        if ($category === null) {
            throw new ApiException('Category not found', 404, 'CATEGORY_NOT_FOUND');
        }

        return $category;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createFromApi(array $data): \Cake\Datasource\EntityInterface
    {
        $this->ensureUniqueName((string)$data['name']);
        $category = $this->newEntity([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
        $this->saveOrFail($category);

        return $category;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateFromApi(int $id, array $data): \Cake\Datasource\EntityInterface
    {
        $category = $this->getOrFailApi($id);
        if (array_key_exists('name', $data) && $data['name'] !== null) {
            $this->ensureUniqueName((string)$data['name'], $id);
            $category->name = (string)$data['name'];
        }
        if (array_key_exists('description', $data)) {
            $category->description = $data['description'];
        }
        $this->saveOrFail($category);

        return $category;
    }

    public function deleteFromApi(int $id): void
    {
        $category = $this->getOrFailApi($id);
        $inUse = $this->Items->find()->where(['Items.category_id' => $id])->count() > 0;
        if ($inUse) {
            throw new ApiException('Category has items and cannot be deleted', 409, 'CATEGORY_IN_USE');
        }
        $this->deleteOrFail($category);
    }

    private function ensureUniqueName(string $name, ?int $excludeId = null): void
    {
        $query = $this->find()->where(['Categories.name' => $name]);
        if ($excludeId !== null) {
            $query->where(['Categories.id !=' => $excludeId]);
        }
        if ($query->count() > 0) {
            throw new ApiException("Category name '{$name}' already exists", 409, 'CATEGORY_NAME_EXISTS');
        }
    }
}
