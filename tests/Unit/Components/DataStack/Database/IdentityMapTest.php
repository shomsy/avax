<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database;

use Avax\Components\DataStack\Database\System\Capabilities\ORM\IdentityMap\IdentityMap;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Tests for the IdentityMap in-memory entity cache.
 */
final class IdentityMapTest extends TestCase
{
    private IdentityMap $identityMap;

    #[Test]
    public function put_stores_entity() : void
    {
        $entity       = new stdClass();
        $entity->name = 'John';

        $this->identityMap->put(entityClass: 'User', id: 1, entity: $entity);
        $retrieved = $this->identityMap->get(entityClass: 'User', id: 1);

        self::assertSame(expected: $entity, actual: $retrieved);
    }

    // ============================================================
    // PUT / GET
    // ============================================================

    #[Test]
    public function get_returns_null_for_unknown_entity() : void
    {
        $result = $this->identityMap->get(entityClass: 'User', id: 999);

        self::assertNull(actual: $result);
    }

    #[Test]
    public function get_returns_null_for_unknown_class() : void
    {
        $entity = new stdClass();
        $this->identityMap->put(entityClass: 'User', id: 1, entity: $entity);

        $result = $this->identityMap->get(entityClass: 'Post', id: 1);

        self::assertNull(actual: $result);
    }

    #[Test]
    public function get_returns_same_object_instance() : void
    {
        $entity       = new stdClass();
        $entity->name = 'John';

        $this->identityMap->put(entityClass: 'User', id: 1, entity: $entity);

        $first  = $this->identityMap->get(entityClass: 'User', id: 1);
        $second = $this->identityMap->get(entityClass: 'User', id: 1);

        self::assertSame(expected: $first, actual: $second);
    }

    // ============================================================
    // IDENTITY GUARANTEE
    // ============================================================

    #[Test]
    public function put_overwrites_existing_entity() : void
    {
        $entity1       = new stdClass();
        $entity1->name = 'John';

        $entity2       = new stdClass();
        $entity2->name = 'Jane';

        $this->identityMap->put(entityClass: 'User', id: 1, entity: $entity1);
        $this->identityMap->put(entityClass: 'User', id: 1, entity: $entity2);

        $retrieved = $this->identityMap->get(entityClass: 'User', id: 1);

        self::assertSame(expected: $entity2, actual: $retrieved);
    }

    #[Test]
    public function integer_id_is_stored_as_string() : void
    {
        $entity = new stdClass();
        $this->identityMap->put(entityClass: 'User', id: 42, entity: $entity);

        $retrieved = $this->identityMap->get(entityClass: 'User', id: 42);
        self::assertSame(expected: $entity, actual: $retrieved);

        $alsoRetrieved = $this->identityMap->get(entityClass: 'User', id: '42');
        self::assertSame(expected: $entity, actual: $alsoRetrieved);
    }

    // ============================================================
    // STRING ID CONVERSION
    // ============================================================

    #[Test]
    public function string_id_works_correctly() : void
    {
        $entity = new stdClass();
        $this->identityMap->put(entityClass: 'User', id: 'uuid-123', entity: $entity);

        $retrieved = $this->identityMap->get(entityClass: 'User', id: 'uuid-123');
        self::assertSame(expected: $entity, actual: $retrieved);
    }

    #[Test]
    public function remove_deletes_entity() : void
    {
        $entity = new stdClass();
        $this->identityMap->put(entityClass: 'User', id: 1, entity: $entity);

        $this->identityMap->remove(entityClass: 'User', id: 1);

        $result = $this->identityMap->get(entityClass: 'User', id: 1);
        self::assertNull(actual: $result);
    }

    // ============================================================
    // REMOVE
    // ============================================================

    #[Test]
    public function remove_nonexistent_entity_is_safe() : void
    {
        $this->identityMap->remove(entityClass: 'User', id: 999);
        // No exception expected
        self::assertTrue(condition: true);
    }

    #[Test]
    public function remove_does_not_affect_other_entities() : void
    {
        $entity1 = new stdClass();
        $entity2 = new stdClass();

        $this->identityMap->put(entityClass: 'User', id: 1, entity: $entity1);
        $this->identityMap->put(entityClass: 'User', id: 2, entity: $entity2);

        $this->identityMap->remove(entityClass: 'User', id: 1);

        self::assertNull(actual: $this->identityMap->get(entityClass: 'User', id: 1));
        self::assertSame(expected: $entity2, actual: $this->identityMap->get(entityClass: 'User', id: 2));
    }

    #[Test]
    public function remove_does_not_affect_other_classes() : void
    {
        $user = new stdClass();
        $post = new stdClass();

        $this->identityMap->put(entityClass: 'User', id: 1, entity: $user);
        $this->identityMap->put(entityClass: 'Post', id: 1, entity: $post);

        $this->identityMap->remove(entityClass: 'User', id: 1);

        self::assertNull(actual: $this->identityMap->get(entityClass: 'User', id: 1));
        self::assertSame(expected: $post, actual: $this->identityMap->get(entityClass: 'Post', id: 1));
    }

    #[Test]
    public function clear_removes_all_entities() : void
    {
        $this->identityMap->put(entityClass: 'User', id: 1, entity: new stdClass());
        $this->identityMap->put(entityClass: 'Post', id: 1, entity: new stdClass());
        $this->identityMap->put(entityClass: 'Comment', id: 1, entity: new stdClass());

        $this->identityMap->clear();

        self::assertNull(actual: $this->identityMap->get(entityClass: 'User', id: 1));
        self::assertNull(actual: $this->identityMap->get(entityClass: 'Post', id: 1));
        self::assertNull(actual: $this->identityMap->get(entityClass: 'Comment', id: 1));
    }

    // ============================================================
    // CLEAR
    // ============================================================

    #[Test]
    public function clear_on_empty_map_is_safe() : void
    {
        $this->identityMap->clear();
        // No exception expected
        self::assertTrue(condition: true);
    }

    #[Test]
    public function stores_multiple_entities_of_same_class() : void
    {
        $user1       = new stdClass();
        $user1->name = 'Alice';
        $user2       = new stdClass();
        $user2->name = 'Bob';
        $user3       = new stdClass();
        $user3->name = 'Charlie';

        $this->identityMap->put(entityClass: 'User', id: 1, entity: $user1);
        $this->identityMap->put(entityClass: 'User', id: 2, entity: $user2);
        $this->identityMap->put(entityClass: 'User', id: 3, entity: $user3);

        self::assertSame(expected: 'Alice', actual: $this->identityMap->get(entityClass: 'User', id: 1)->name);
        self::assertSame(expected: 'Bob', actual: $this->identityMap->get(entityClass: 'User', id: 2)->name);
        self::assertSame(expected: 'Charlie', actual: $this->identityMap->get(entityClass: 'User', id: 3)->name);
    }

    // ============================================================
    // MULTIPLE ENTITIES
    // ============================================================

    #[Test]
    public function stores_multiple_entity_classes() : void
    {
        $user       = new stdClass();
        $user->name = 'Alice';

        $post        = new stdClass();
        $post->title = 'Hello World';

        $comment       = new stdClass();
        $comment->body = 'Nice post!';

        $this->identityMap->put(entityClass: 'User', id: 1, entity: $user);
        $this->identityMap->put(entityClass: 'Post', id: 1, entity: $post);
        $this->identityMap->put(entityClass: 'Comment', id: 1, entity: $comment);

        self::assertInstanceOf(expected: stdClass::class, actual: $this->identityMap->get(entityClass: 'User', id: 1));
        self::assertInstanceOf(expected: stdClass::class, actual: $this->identityMap->get(entityClass: 'Post', id: 1));
        self::assertInstanceOf(expected: stdClass::class, actual: $this->identityMap->get(entityClass: 'Comment', id: 1));
    }

    #[Test]
    public function handles_zero_id() : void
    {
        $entity = new stdClass();
        $this->identityMap->put(entityClass: 'User', id: 0, entity: $entity);

        $retrieved = $this->identityMap->get(entityClass: 'User', id: 0);
        self::assertSame(expected: $entity, actual: $retrieved);
    }

    // ============================================================
    // ZERO AND NULL IDS
    // ============================================================

    #[Test]
    public function handles_null_id() : void
    {
        $entity = new stdClass();
        $this->identityMap->put(entityClass: 'User', id: null, entity: $entity);

        $retrieved = $this->identityMap->get(entityClass: 'User', id: null);
        self::assertSame(expected: $entity, actual: $retrieved);
    }

    protected function setUp() : void
    {
        $this->identityMap = new IdentityMap();
    }
}
