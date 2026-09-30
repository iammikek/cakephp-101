<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Exception\ApiException;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;

class ItemsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('items');
        $this->setPrimaryKey('id');
        $this->belongsTo('Categories', [
            'foreignKey' => 'category_id',
        ]);
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function listFiltered(array $filters): SelectQuery
    {
        $query = $this->find()
            ->contain(['Categories'])
            ->orderBy(['Items.id' => 'ASC']);

        if (isset($filters['min_price'])) {
            $query->where(['Items.price >=' => $filters['min_price']]);
        }
        if (isset($filters['max_price'])) {
            $query->where(['Items.price <=' => $filters['max_price']]);
        }
        if (isset($filters['category_id'])) {
            $query->where(['Items.category_id' => $filters['category_id']]);
        }
        if (isset($filters['name_contains'])) {
            $query->where([
                'LOWER(Items.name) LIKE' => '%' . strtolower((string)$filters['name_contains']) . '%',
            ]);
        }

        return $query;
    }

    public function getOrFailApi(int $id): \Cake\Datasource\EntityInterface
    {
        $item = $this->find()
            ->contain(['Categories'])
            ->where(['Items.id' => $id])
            ->first();
        if ($item === null) {
            throw new ApiException('Item not found', 404, 'ITEM_NOT_FOUND');
        }

        return $item;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createFromApi(array $data): \Cake\Datasource\EntityInterface
    {
        if (isset($data['category_id']) && $data['category_id'] !== null) {
            $this->Categories->getOrFailApi((int)$data['category_id']);
        }

        $item = $this->newEntity([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'category_id' => $data['category_id'] ?? null,
        ]);
        $this->saveOrFail($item);

        return $this->getOrFailApi((int)$item->id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateFromApi(int $id, array $data): \Cake\Datasource\EntityInterface
    {
        $item = $this->getOrFailApi($id);
        if (array_key_exists('name', $data) && $data['name'] !== null) {
            $item->name = (string)$data['name'];
        }
        if (array_key_exists('description', $data)) {
            $item->description = $data['description'];
        }
        if (array_key_exists('price', $data) && $data['price'] !== null) {
            $item->price = $data['price'];
        }
        if (array_key_exists('category_id', $data)) {
            if ($data['category_id'] === null) {
                $item->category_id = null;
            } else {
                $category = $this->Categories->getOrFailApi((int)$data['category_id']);
                $item->category_id = (int)$category->id;
            }
        }
        $this->saveOrFail($item);

        return $this->getOrFailApi($id);
    }

    public function deleteFromApi(int $id): void
    {
        $item = $this->getOrFailApi($id);
        $this->deleteOrFail($item);
    }

    /**
     * @return array<string, mixed>
     */
    public function stats(): array
    {
        $total = $this->find()->count();
        if ($total === 0) {
            return [
                'total_items' => 0,
                'average_price' => 0.0,
                'min_price' => null,
                'max_price' => null,
                'uncategorized_count' => 0,
                'by_category' => [],
            ];
        }

        $connection = $this->getConnection();
        $aggregate = $connection->execute(
            'SELECT AVG(CAST(price AS REAL)) AS avg_price, MIN(CAST(price AS REAL)) AS min_price, MAX(CAST(price AS REAL)) AS max_price FROM items',
        )->fetch('assoc');

        $uncategorized = $this->find()->where(['Items.category_id IS' => null])->count();

        $rows = $connection->execute(
            'SELECT categories.id AS category_id, categories.name AS category_name,
                    COUNT(items.id) AS item_count, AVG(CAST(items.price AS REAL)) AS average_price
             FROM items
             INNER JOIN categories ON categories.id = items.category_id
             GROUP BY categories.id, categories.name
             ORDER BY categories.name',
        )->fetchAll('assoc');

        $byCategory = [];
        foreach ($rows as $row) {
            $byCategory[] = [
                'category_id' => (int)$row['category_id'],
                'category_name' => $row['category_name'],
                'item_count' => (int)$row['item_count'],
                'average_price' => round((float)$row['average_price'], 2),
            ];
        }

        return [
            'total_items' => $total,
            'average_price' => round((float)$aggregate['avg_price'], 2),
            'min_price' => round((float)$aggregate['min_price'], 2),
            'max_price' => round((float)$aggregate['max_price'], 2),
            'uncategorized_count' => $uncategorized,
            'by_category' => $byCategory,
        ];
    }
}
