<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Axiom\Collections\Collection;

class CollectionTest extends TestCase
{
    public function test_static_make_method()
    {
        $collection = Collection::make(['foo', 'bar', 'baz']);
        $this->assertSame(['foo', 'bar', 'baz'], $collection->all());
    }

    public function test_all_method()
    {
        $collection = new Collection(['foo', 'bar', 'baz']);
        $this->assertSame(['foo', 'bar', 'baz'], $collection->all());
    }

    public function get_method_provider()
    {
        return [
            [['foo', 'bar', 'baz'], 'bar', 1],
            [['foo' => 'bar', 'lorem' => 'ipsum'], 'ipsum', 'lorem'],
        ];
    }

    /**
     * @dataProvider get_method_provider
     */
    public function test_get_method($data, $expect, $key)
    {
        $collection = new Collection($data);
        $this->assertSame($expect, $collection->get($key));
    }

    public function test_push_method()
    {
        $collection = new Collection('foo');
        $collection->push('bar');

        $this->assertSame(['foo', 'bar'], $collection->all());
    }

    public function test_put_method()
    {
        $collection = new Collection(['lorem' => 'bar']);
        $collection->put('lorem', 'ipsum');

        $this->assertSame(['lorem' => 'ipsum'], $collection->all());
    }

    public function test_remove_method()
    {
        $collection = new Collection(['foo' => 'bar', 'lorem' => 'ipsum']);
        $collection->remove('lorem');

        $this->assertSame(['foo' => 'bar'], $collection->all());
    }

    public function test_has_method()
    {
        $collection = new Collection(['foo' => 'bar']);

        $this->assertTrue($collection->has('foo'));
        $this->assertFalse($collection->has('bar'));
    }

    public function test_count_method()
    {
        $collection = new Collection(['foo', 'bar', 'baz', 'lorem', 'ipsum']);

        $this->assertSame(5, $collection->count());
    }

    public function test_first_method()
    {
        $collection = new Collection(['foo', 'bar', 'baz']);

        $this->assertSame('foo', $collection->first());
    }

    public function test_last_method()
    {
        $collection = new Collection(['foo', 'bar', 'baz']);

        $this->assertSame('baz', $collection->last());
    }

    public function test_each_method()
    {
        $collection = new Collection(['foo', 'bar', 'baz']);

        $result = [];

        $collection->each(function ($item, $key) use (&$result) {
            $result[$key] = $item;
        });

        $this->assertEquals($result, $collection->all());
    }

    public function test_each_method_break()
    {
        $collection = new Collection([1, 2, 3, 'foo', 'bar', 'baz']);

        $result = [];

        $collection->each(function ($item, $key) use (&$result) {
            $result[$key] = $item;

            if (is_string($item)) {
                return false;
            }
        });

        $this->assertEquals([1, 2, 3, 'foo'], $result);
    }

    public function test_map_method()
    {
        $collection = new Collection([1, 2, 3, 4, 5]);

        $mapped = $collection->map(function ($item, $key) {
            return $item * 2;
        });

        $this->assertSame([2, 4, 6, 8, 10], $mapped->all());
    }

    public function test_filter_method()
    {
        $collection = new Collection([
            ['title' => 'Foo', 'price' => 86],
            ['title' => 'Bar', 'price' => 132],
            ['title' => 'Lorem', 'price' => 102],
            ['title' => 'Ipsum', 'price' => 13],
        ]);

        $expensiveItems = $collection->filter(function ($item, $key) {
            return $item['price'] > 100;
        });

        $this->assertSame([
            ['title' => 'Bar', 'price' => 132],
            ['title' => 'Lorem', 'price' => 102],
        ], $expensiveItems->all());
    }

    public function test_reject_method()
    {
        $collection = new Collection([
            ['title' => 'Foo', 'price' => 86],
            ['title' => 'Bar', 'price' => 132],
            ['title' => 'Lorem', 'price' => 102],
            ['title' => 'Ipsum', 'price' => 13],
        ]);

        $affordableItems = $collection->reject(function ($item, $key) {
            return $item['price'] > 100;
        });

        $this->assertSame([
            ['title' => 'Foo', 'price' => 86],
            ['title' => 'Ipsum', 'price' => 13],
        ], $affordableItems->all());
    }

    public function test_filter_map_methods()
    {
        $collection = new Collection([
            ['name' => 'John Doe', 'email' => 'john@example.com'],
            ['name' => 'Alan Doe', 'email' => 'alan@example.com'],
            ['name' => 'Jane Doe', 'email' => null],
            ['name' => 'Zoe Doe', 'email' => null],
        ]);

        $userEmails = $collection->filter(function ($item, $key) {
            return $item['email'] !== null;
        })->map(function ($item, $key) {
            return $item['email'];
        });

        $this->assertSame([
            'john@example.com',
            'alan@example.com',
        ], $userEmails->all());
    }

    public function test_map_filter_methods_separately()
    {
        $employees = new Collection([
            ['name' => 'Mary', 'email' => 'mary@example.com', 'salaried' => true],
            ['name' => 'John', 'email' => 'john@example.com', 'salaried' => false],
            ['name' => 'Kelly', 'email' => 'kelly@example.com', 'salaried' => true],
        ]);

        $employeeEmails = $employees->map(function ($employee) {
            return $employee['email'];
        });

        $salariedEmployees = $employees->filter(function ($employee) {
            return $employee['salaried'];
        });

        $this->assertSame([
            'mary@example.com',
            'john@example.com',
            'kelly@example.com',
        ], $employeeEmails->all());

        $this->assertSame([
            ['name' => 'Mary', 'email' => 'mary@example.com', 'salaried' => true],
            ['name' => 'Kelly', 'email' => 'kelly@example.com', 'salaried' => true],
        ], $salariedEmployees->all());
    }

    public function test_array_access_implementation()
    {
        $collection = Collection::make([1, 2, 3]);

        $this->assertSame(3, $collection[2]);
    }

    public function test_values_method()
    {
        $collection = Collection::make([
            'lorem' => 'foo',
            'ipsum' => 'bar',
            'dolar' => 'baz',
        ]);

        $values = $collection->values();

        $this->assertSame(['foo', 'bar', 'baz'], $values->all());
    }

    public function test_keys_method()
    {
        $collection = Collection::make([
            'lorem' => 'foo',
            'ipsum' => 'bar',
            'dolar' => 'baz',
        ]);

        $keys = $collection->keys();

        $this->assertSame(['lorem', 'ipsum', 'dolar'], $keys->all());
    }

    public function test_flatten_method()
    {
        $collection = Collection::make([
            'name'    => 'Kai',
            'profile' => [
                'age'            => 28,
                'favorite_games' => ['Mass Effect', 'Oxygen Not Included', 'event[0]'],
            ],
        ]);

        $flattened = $collection->flatten();

        $this->assertSame([
            'Kai', 28, 'Mass Effect', 'Oxygen Not Included', 'event[0]',
        ], $flattened->all());
    }

    public function test_sort_method()
    {
        $collection = Collection::make([4, 1, 3, 2, 5]);
        $this->assertSame([1, 2, 3, 4, 5], $collection->sort()->values()->all());

        $collection = Collection::make([-4, 1, 3, -3, -2, 0, -5, 2, 4, -1, 5]);
        $this->assertSame([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], $collection->sort()->values()->all());

        $collection = Collection::make(['foo3', 'bar1', 'foo2', 'foo1', 'bar2', 'bar3']);
        $this->assertSame(['bar1', 'bar2', 'bar3', 'foo1', 'foo2', 'foo3'], $collection->sort()->values()->all());
    }

    public function test_sort_method_with_callback()
    {
        $collection = Collection::make([4, 1, 3, 2, 5])->sort(function ($a, $b) {
            if ($a === $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        });

        $this->assertEquals([1, 2, 3, 4, 5], $collection->values()->all());
    }

    public function test_sort_by_key_method()
    {
        $collection = Collection::make([
            'foo4' => 1,
            'foo2' => 2,
            'foo5' => 3,
            'foo3' => 4,
            'foo1' => 5,
        ]);

        $this->assertEquals([5, 2, 4, 1, 3], $collection->sortByKey()->values()->all());
    }

    public function test_sort_by_key_method_with_callback()
    {
        $collection = Collection::make([
            'foo4' => 1,
            'foo2' => 2,
            'foo5' => 3,
            'foo3' => 4,
            'foo1' => 5,
        ])->sortByKey(function ($a, $b) {
            if ($a === $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        });

        $this->assertEquals([5, 2, 4, 1, 3], $collection->values()->all());
    }

    public function test_reverse_method()
    {
        $collection = Collection::make([4, 1, 3, 2, 5])->reverse();
        $this->assertEquals([5, 2, 3, 1, 4], $collection->values()->all());

        $collection = Collection::make([4, 1, 3, 2, 5])->sort()->reverse();
        $this->assertEquals([5, 4, 3, 2, 1], $collection->values()->all());
    }

    public function test_group_by_method()
    {
        $data = new Collection([
            'hello bot'    => ['type' => 'atomic', 'responses' => []],
            'my name is _' => ['type' => 'alphabetic', 'responses' => []],
            'i am #'       => ['type' => 'numeric', 'responses' => []],
            'i like *'     => ['type' => 'global', 'responses' => []],
            'yes'          => ['type' => 'atomic', 'responses' => []],
        ]);

        $result = $data->groupBy(function ($item) {
            return $item['type'];
        });

        $expected = [
            'atomic' => [
                'hello bot'    => ['type' => 'atomic', 'responses' => []],
                'yes'          => ['type' => 'atomic', 'responses' => []],
            ],
            'alphabetic' => [
                'my name is _' => ['type' => 'alphabetic', 'responses' => []],
            ],
            'numeric' => [
                'i am #'       => ['type' => 'numeric', 'responses' => []],
            ],
            'global' => [
                'i like *'     => ['type' => 'global', 'responses' => []],
            ],
        ];

        $this->assertEquals($expected, $result->all());
    }

    public function test_get_Iterator()
    {
        $collection = Collection::make([4, 1, 3, 2, 5]);

        $this->assertSame([4, 1, 3, 2, 5], $collection->getIterator()->getArrayCopy());
    }

    public function test_offset_is_existed()
    {
        $collection = Collection::make(['foo' => 'bar', 'lorem' => 'ipsum']);

        $this->assertTrue($collection->offsetExists('foo'));
        $this->assertFalse($collection->offsetExists('ipsum'));
    }

    public function offset_can_set_value_provider()
    {
        return [
            [['foo' => 'bar', 'lorem' => 'ipsum'], 'key', 'value', ['foo' => 'bar', 'lorem' => 'ipsum', 'key' => 'value']],
            [[1, 2, 3, 4], null, 5, [1, 2, 3, 4, 5]]
        ];
    }

    /**
     * @dataProvider offset_can_set_value_provider
     */
    public function test_offset_can_set_value($data, $offset, $value, $expect)
    {
        $collection = Collection::make($data);
        $collection->offsetSet($offset, $value);

        $this->assertSame($expect, $collection->all());
    }

    public function offset_can_unset_value_provider()
    {
        return [
            [['foo' => 'bar', 'lorem' => 'ipsum'], 'foo'],
            [[1, 2, 3, 4], 0]
        ];
    }

    /**
     * @dataProvider offset_can_unset_value_provider
     */
    public function test_offset_can_unset_value($data, $unsetKey)
    {
        $collection = Collection::make($data);
        $collection->offsetUnset($unsetKey);

        $this->assertFalse($collection->offsetExists($unsetKey));
    }
}
