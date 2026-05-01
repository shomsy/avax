<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Persistence;

use Avax\Components\DataStack\Persistence\System\Capabilities\Diagnostics\DetectNPlusOneQuery;
use Avax\Components\DataStack\Persistence\System\Capabilities\Diagnostics\NPlusOneQueryReport;
use Avax\Components\DataStack\Persistence\System\Capabilities\Diagnostics\PersistenceTimeline;
use Avax\Components\DataStack\Persistence\System\Capabilities\Diagnostics\QueryFingerprint;
use Avax\Components\DataStack\Persistence\System\Capabilities\QueryIntent\DataQuery;
use Avax\Components\DataStack\Persistence\System\Capabilities\QueryIntent\DataQueryBuilder;
use Avax\Components\DataStack\Persistence\System\Capabilities\QueryIntent\DataQueryPlan;
use Avax\Components\DataStack\Persistence\System\Capabilities\QueryIntent\QueryResult;
use Avax\Components\DataStack\Persistence\System\Flows\BuildDataQuery\BuildDataQuery;
use Avax\Components\DataStack\Persistence\System\Flows\CompileDataQuery\CompileDataQuery;
use Avax\Components\DataStack\Persistence\System\Flows\ExecuteDataQuery\ExecuteDataQuery;
use Avax\Components\DataStack\Persistence\System\Flows\ExplainDataQuery\ExplainDataQuery;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class QueryIntentTest extends TestCase
{
    // ==================== DataQuery Tests ====================

    public function test_data_query_default_values() : void
    {
        $query = new DataQuery();

        $this->assertNull($query->entityType);
        $this->assertEmpty($query->conditions);
        $this->assertEmpty($query->orderBy);
        $this->assertNull($query->limit);
        $this->assertNull($query->offset);
        $this->assertEmpty($query->joins);
        $this->assertSame(['*'], $query->select);
    }

    public function test_data_query_with_condition() : void
    {
        $query = new DataQuery();
        $newQuery = $query->withCondition('status', 'active', '=');

        $this->assertNotSame($query, $newQuery);
        $this->assertCount(1, $newQuery->conditions);
        $this->assertSame('status', $newQuery->conditions[0]['field']);
        $this->assertSame('active', $newQuery->conditions[0]['value']);
        $this->assertSame('=', $newQuery->conditions[0]['operator']);
    }

    public function test_data_query_with_multiple_conditions() : void
    {
        $query = new DataQuery();
        $newQuery = $query
            ->withCondition('status', 'active')
            ->withCondition('age', 18, '>=');

        $this->assertCount(2, $newQuery->conditions);
    }

    public function test_data_query_with_order_by() : void
    {
        $query = new DataQuery();
        $newQuery = $query->withOrderBy('created_at', 'DESC');

        $this->assertSame(['created_at' => 'DESC'], $newQuery->orderBy);
    }

    public function test_data_query_with_limit() : void
    {
        $query = new DataQuery();
        $newQuery = $query->withLimit(10);

        $this->assertSame(10, $newQuery->limit);
    }

    public function test_data_query_with_offset() : void
    {
        $query = new DataQuery();
        $newQuery = $query->withOffset(20);

        $this->assertSame(20, $newQuery->offset);
    }

    public function test_data_query_with_joins() : void
    {
        $query = new DataQuery();
        $joins = [
            ['type' => 'LEFT', 'table' => 'users', 'on' => 'posts.user_id = users.id'],
        ];
        $newQuery = $query->withJoins($joins);

        $this->assertCount(1, $newQuery->joins);
        $this->assertSame('users', $newQuery->joins[0]['table']);
    }

    public function test_data_query_with_select() : void
    {
        $query = new DataQuery();
        $newQuery = $query->withSelect(['id', 'name', 'email']);

        $this->assertSame(['id', 'name', 'email'], $newQuery->select);
    }

    public function test_data_query_immutability() : void
    {
        $query = new DataQuery(
            entityType: 'User',
            conditions: [],
            limit     : 10,
        );

        $newQuery = $query->withLimit(20);

        $this->assertSame(10, $query->limit);
        $this->assertSame(20, $newQuery->limit);
    }

    // ==================== DataQueryBuilder Tests ====================

    public function test_query_builder_fluent_interface() : void
    {
        $builder = new DataQueryBuilder();
        $query   = $builder
            ->from('User')
            ->select(['id', 'name'])
            ->where('status', 'active')
            ->where('age', 18, '>=')
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->offset(20)
            ->leftJoin('profiles', 'users.id = profiles.user_id')
            ->build();

        $this->assertSame('User', $query->entityType);
        $this->assertSame(['id', 'name'], $query->select);
        $this->assertCount(2, $query->conditions);
        $this->assertSame(['created_at' => 'DESC'], $query->orderBy);
        $this->assertSame(10, $query->limit);
        $this->assertSame(20, $query->offset);
        $this->assertCount(1, $query->joins);
    }

    public function test_query_builder_reset() : void
    {
        $builder = new DataQueryBuilder();
        $builder
            ->from('User')
            ->where('status', 'active')
            ->limit(10);

        $builder->reset();
        $query = $builder->build();

        $this->assertNull($query->entityType);
        $this->assertEmpty($query->conditions);
        $this->assertNull($query->limit);
    }

    public function test_query_builder_inner_join() : void
    {
        $builder = new DataQueryBuilder();
        $query   = $builder
            ->from('Post')
            ->innerJoin('users', 'posts.user_id = users.id')
            ->build();

        $this->assertCount(1, $query->joins);
        $this->assertSame('INNER', $query->joins[0]['type']);
    }

    public function test_query_builder_select_string() : void
    {
        $builder = new DataQueryBuilder();
        $query   = $builder->select('id')->build();

        $this->assertSame(['id'], $query->select);
    }

    // ==================== DataQueryPlan Tests ====================

    public function test_query_plan_explain() : void
    {
        $plan = new DataQueryPlan(
            sql          : 'SELECT * FROM users WHERE status = ?',
            bindings     : ['active'],
            estimatedCost: 15.5,
            indexesUsed  : ['idx_status'],
        );

        $explanation = $plan->explain();

        $this->assertStringContainsString('SELECT * FROM users', $explanation);
        $this->assertStringContainsString('Bindings', $explanation);
        $this->assertStringContainsString('Estimated Cost', $explanation);
        $this->assertStringContainsString('Indexes Used', $explanation);
    }

    public function test_query_plan_is_optimized() : void
    {
        $optimizedPlan = new DataQueryPlan(sql: 'SELECT * FROM users');
        $this->assertTrue($optimizedPlan->isOptimized());

        $unoptimizedPlan = new DataQueryPlan(
            sql        : 'SELECT * FROM users',
            suggestions: ['Add index on status'],
        );
        $this->assertFalse($unoptimizedPlan->isOptimized());
    }

    public function test_query_plan_suggestions() : void
    {
        $plan = new DataQueryPlan(
            sql        : 'SELECT * FROM users',
            suggestions: ['Add index', 'Use LIMIT'],
        );

        $this->assertSame(['Add index', 'Use LIMIT'], $plan->suggestions());
    }

    public function test_query_plan_with_methods() : void
    {
        $plan = new DataQueryPlan();

        $newPlan = $plan
            ->withSql('SELECT * FROM users')
            ->withBindings(['active'])
            ->withEstimatedCost(10.0)
            ->withIndexesUsed(['idx_status']);

        $this->assertSame('SELECT * FROM users', $newPlan->sql);
        $this->assertSame(['active'], $newPlan->bindings);
        $this->assertSame(10.0, $newPlan->estimatedCost);
        $this->assertSame(['idx_status'], $newPlan->indexesUsed);
    }

    // ==================== QueryResult Tests ====================

    public function test_query_result_rows() : void
    {
        $rows = [
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
        ];
        $result = new QueryResult(rows: $rows, count: 2);

        $this->assertSame($rows, $result->rows());
        $this->assertSame(2, $result->count());
    }

    public function test_query_result_to_array() : void
    {
        $rows = [['id' => 1]];
        $result = new QueryResult(rows: $rows, count: 1);

        $this->assertSame($rows, $result->toArray());
    }

    public function test_query_result_is_empty() : void
    {
        $emptyResult = new QueryResult();
        $this->assertTrue($emptyResult->isEmpty());

        $nonEmptyResult = new QueryResult(rows: [['id' => 1]], count: 1);
        $this->assertFalse($nonEmptyResult->isEmpty());
    }

    public function test_query_result_first() : void
    {
        $rows = [
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
        ];
        $result = new QueryResult(rows: $rows, count: 2);

        $this->assertSame(['id' => 1, 'name' => 'Alice'], $result->first());
    }

    public function test_query_result_first_empty() : void
    {
        $result = new QueryResult();
        $this->assertNull($result->first());
    }

    public function test_query_result_took_ms() : void
    {
        $result = new QueryResult(tookMs: 45.5, count: 0);
        $this->assertSame(45.5, $result->tookMs());
    }

    // ==================== BuildDataQuery Flow Tests ====================

    public function test_build_data_query_basic() : void
    {
        $flow = new BuildDataQuery();
        $query = $flow->build(
            entityType: 'stdClass',
            conditions: ['status' => 'active'],
            orderBy   : ['created_at' => 'DESC'],
            limit     : 10,
            offset    : 0,
        );

        $this->assertSame('stdClass', $query->entityType);
        $this->assertSame(['status' => 'active'], $query->conditions);
        $this->assertSame(['created_at' => 'DESC'], $query->orderBy);
        $this->assertSame(10, $query->limit);
    }

    public function test_build_data_query_with_entity_registry() : void
    {
        $flow = new BuildDataQuery(['user' => 'stdClass']);
        $query = $flow->build(entityType: 'user');

        $this->assertSame('stdClass', $query->entityType);
    }

    public function test_build_data_query_invalid_entity() : void
    {
        $this->expectException(InvalidArgumentException::class);

        $flow = new BuildDataQuery();
        $flow->build(entityType: 'NonExistentEntity');
    }

    public function test_build_data_query_invalid_limit() : void
    {
        $this->expectException(InvalidArgumentException::class);

        $flow = new BuildDataQuery(['user' => 'stdClass']);
        $flow->build(entityType: 'user', limit: -1);
    }

    public function test_build_data_query_invalid_offset() : void
    {
        $this->expectException(InvalidArgumentException::class);

        $flow = new BuildDataQuery(['user' => 'stdClass']);
        $flow->build(entityType: 'user', offset: -1);
    }

    public function test_build_data_query_invalid_order_direction() : void
    {
        $this->expectException(InvalidArgumentException::class);

        $flow = new BuildDataQuery(['user' => 'stdClass']);
        $flow->build(entityType: 'user', orderBy: ['name' => 'INVALID']);
    }

    // ==================== CompileDataQuery Flow Tests ====================

    public function test_compile_data_query_basic() : void
    {
        $flow  = new CompileDataQuery();
        $query = new DataQuery(
            entityType: 'User',
            conditions: [
                            ['field' => 'status', 'value' => 'active', 'operator' => '='],
                        ],
            limit     : 10,
            offset    : 0,
        );

        $plan = $flow->compile($query);

        $this->assertStringContainsString('SELECT', $plan->sql);
        $this->assertStringContainsString('FROM user', $plan->sql);
        $this->assertStringContainsString('WHERE', $plan->sql);
        $this->assertStringContainsString('LIMIT', $plan->sql);
        $this->assertSame(['active'], $plan->bindings);
    }

    public function test_compile_data_query_with_joins() : void
    {
        $flow  = new CompileDataQuery();
        $query = new DataQuery(
            entityType: 'Post',
            joins     : [
                            ['type' => 'LEFT', 'table' => 'users', 'on' => 'posts.user_id = users.id'],
                        ],
        );

        $plan = $flow->compile($query);

        $this->assertStringContainsString('LEFT JOIN', $plan->sql);
        $this->assertStringContainsString('users', $plan->sql);
    }

    public function test_compile_data_query_with_order_by() : void
    {
        $flow = new CompileDataQuery();
        $query = new DataQuery(
            entityType: 'User',
            orderBy   : ['name' => 'ASC', 'created_at' => 'DESC'],
        );

        $plan = $flow->compile($query);

        $this->assertStringContainsString('ORDER BY', $plan->sql);
        $this->assertStringContainsString('name ASC', $plan->sql);
        $this->assertStringContainsString('created_at DESC', $plan->sql);
    }

    public function test_compile_data_query_without_entity_type() : void
    {
        $this->expectException(InvalidArgumentException::class);

        $flow  = new CompileDataQuery();
        $query = new DataQuery();
        $flow->compile($query);
    }

    public function test_compile_data_query_table_name_conversion() : void
    {
        $flow = new CompileDataQuery();
        $query = new DataQuery(entityType: 'UserProfile');

        $plan = $flow->compile($query);

        $this->assertStringContainsString('FROM user_profile', $plan->sql);
    }

    // ==================== ExecuteDataQuery Flow Tests ====================

    public function test_execute_data_query_with_executor() : void
    {
        $mockRows = [
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
        ];

        $executor = static fn () => $mockRows;
        $flow = new ExecuteDataQuery($executor);
        $plan = new DataQueryPlan(sql: 'SELECT * FROM users');

        $result = $flow->execute($plan);

        $this->assertSame($mockRows, $result->rows());
        $this->assertSame(2, $result->count());
        $this->assertNotNull($result->tookMs());
        $this->assertNotNull($result->fingerprint);
    }

    public function test_execute_data_query_default_executor() : void
    {
        $flow = new ExecuteDataQuery();
        $plan = new DataQueryPlan(sql: 'SELECT * FROM users');

        $result = $flow->execute($plan);

        $this->assertEmpty($result->rows());
        $this->assertSame(0, $result->count());
    }

    // ==================== ExplainDataQuery Flow Tests ====================

    public function test_explain_data_query_no_conditions_warning() : void
    {
        $flow = new ExplainDataQuery();
        $query = new DataQuery(
            entityType: 'User',
            select    : ['*'],
        );

        $plan = $flow->explain($query);

        $this->assertFalse($plan->isOptimized());
        $suggestions = $plan->suggestions();
        $this->assertNotEmpty($suggestions);
    }

    public function test_explain_data_query_optimized() : void
    {
        $flow = new ExplainDataQuery();
        $query = new DataQuery(
            entityType: 'User',
            conditions: [['field' => 'id', 'value' => 1, 'operator' => '=']],
            select    : ['id', 'name'],
            limit     : 1,
        );

        $plan = $flow->explain($query);

        // This should have fewer or no suggestions
        $this->assertIsArray($plan->suggestions());
    }

    public function test_explain_data_query_select_star_warning() : void
    {
        $flow = new ExplainDataQuery();
        $query = new DataQuery(
            entityType: 'User',
            select    : ['*'],
            conditions: [['field' => 'id', 'value' => 1, 'operator' => '=']],
            limit     : 10,
        );

        $plan = $flow->explain($query);
        $suggestions = $plan->suggestions();

        $hasSelectStarWarning = false;
        foreach ($suggestions as $suggestion) {
            if (str_contains($suggestion, 'SELECT *')) {
                $hasSelectStarWarning = true;

                break;
            }
        }
        $this->assertTrue($hasSelectStarWarning);
    }

    // ==================== QueryFingerprint Tests ====================

    public function test_query_fingerprint_normalize_strings() : void
    {
        $fingerprint = QueryFingerprint::fromQuery("SELECT * FROM users WHERE name = 'John'");

        $this->assertStringContainsString('?', $fingerprint->pattern());
        $this->assertStringNotContainsString('John', $fingerprint->pattern());
    }

    public function test_query_fingerprint_normalize_numbers() : void
    {
        $fingerprint = QueryFingerprint::fromQuery('SELECT * FROM users WHERE id = 123 AND age > 18');

        $this->assertStringContainsString('?', $fingerprint->pattern());
        $this->assertStringNotContainsString('123', $fingerprint->pattern());
        $this->assertStringNotContainsString('18', $fingerprint->pattern());
    }

    public function test_query_fingerprint_normalize_whitespace() : void
    {
        $fingerprint = QueryFingerprint::fromQuery('SELECT   *   FROM   users');

        $this->assertSame('SELECT * FROM users', $fingerprint->pattern());
    }

    public function test_query_fingerprint_hash_consistency() : void
    {
        $fingerprint1 = QueryFingerprint::fromQuery('SELECT * FROM users WHERE id = 1');
        $fingerprint2 = QueryFingerprint::fromQuery('SELECT * FROM users WHERE id = 2');

        $this->assertSame($fingerprint1->hash(), $fingerprint2->hash());
    }

    public function test_query_fingerprint_matches() : void
    {
        $fingerprint = QueryFingerprint::fromQuery('SELECT * FROM users WHERE id = 1');

        $this->assertTrue($fingerprint->matches('SELECT * FROM users WHERE id = 2'));
        $this->assertFalse($fingerprint->matches('SELECT * FROM posts WHERE id = 1'));
    }

    public function test_query_fingerprint_matches_fingerprint() : void
    {
        $fingerprint1 = QueryFingerprint::fromQuery('SELECT * FROM users WHERE id = 1');
        $fingerprint2 = QueryFingerprint::fromQuery('SELECT * FROM users WHERE id = 2');

        $this->assertTrue($fingerprint1->matchesFingerprint($fingerprint2));
    }

    public function test_query_fingerprint_to_string() : void
    {
        $fingerprint = QueryFingerprint::fromQuery('SELECT * FROM users WHERE id = 1');

        $this->assertSame($fingerprint->pattern(), (string) $fingerprint);
    }

    // ==================== DetectNPlusOneQuery Tests ====================

    public function test_detect_n_plus_one_below_threshold() : void
    {
        $detector = new DetectNPlusOneQuery(threshold: 5);

        for ($i = 0; $i < 3; $i++) {
            $detector->record("SELECT * FROM users WHERE id = {$i}");
        }

        $reports = $detector->detect();
        $this->assertEmpty($reports);
    }

    public function test_detect_n_plus_one_at_threshold() : void
    {
        $detector = new DetectNPlusOneQuery(threshold: 5);

        for ($i = 0; $i < 5; $i++) {
            $detector->record("SELECT * FROM users WHERE id = {$i}");
        }

        $reports = $detector->detect();
        $this->assertCount(1, $reports);
    }

    public function test_detect_n_plus_one_above_threshold() : void
    {
        $detector = new DetectNPlusOneQuery(threshold: 10);

        for ($i = 0; $i < 15; $i++) {
            $detector->record("SELECT * FROM users WHERE id = {$i}");
        }

        $reports = $detector->detect();
        $this->assertCount(1, $reports);
        $this->assertSame(15, $reports[0]->count());
    }

    public function test_detect_n_plus_one_different_patterns() : void
    {
        $detector = new DetectNPlusOneQuery(threshold: 5);

        for ($i = 0; $i < 5; $i++) {
            $detector->record("SELECT * FROM users WHERE id = {$i}");
        }

        for ($i = 0; $i < 5; $i++) {
            $detector->record("SELECT * FROM posts WHERE user_id = {$i}");
        }

        $reports = $detector->detect();
        $this->assertCount(2, $reports);
    }

    public function test_detect_n_plus_one_report_content() : void
    {
        $detector = new DetectNPlusOneQuery(threshold: 3);

        for ($i = 0; $i < 3; $i++) {
            $detector->record("SELECT * FROM users WHERE id = {$i}");
        }

        $reports = $detector->detect();
        $report = $reports[0];

        $this->assertInstanceOf(NPlusOneQueryReport::class, $report);
        $this->assertSame(3, $report->count());
        $this->assertNotEmpty($report->sampleQueries());
        $this->assertNotNull($report->suggestion());
    }

    public function test_detect_n_plus_one_reset() : void
    {
        $detector = new DetectNPlusOneQuery(threshold: 3);

        for ($i = 0; $i < 3; $i++) {
            $detector->record("SELECT * FROM users WHERE id = {$i}");
        }

        $detector->reset();
        $reports = $detector->detect();

        $this->assertEmpty($reports);
    }

    public function test_detect_n_plus_one_threshold_change() : void
    {
        $detector = new DetectNPlusOneQuery(threshold: 10);
        $this->assertSame(10, $detector->getThreshold());

        $detector->setThreshold(5);
        $this->assertSame(5, $detector->getThreshold());
    }

    public function test_detect_n_plus_one_pattern_count() : void
    {
        $detector = new DetectNPlusOneQuery(threshold: 5);

        for ($i = 0; $i < 5; $i++) {
            $detector->record("SELECT * FROM users WHERE id = {$i}");
        }

        for ($i = 0; $i < 5; $i++) {
            $detector->record("SELECT * FROM posts WHERE id = {$i}");
        }

        $this->assertSame(2, $detector->getPatternCount());
    }

    // ==================== NPlusOneQueryReport Tests ====================

    public function test_n_plus_one_report_severity() : void
    {
        $fingerprint = QueryFingerprint::fromQuery('SELECT * FROM users WHERE id = ?');
        $report = new NPlusOneQueryReport(
            pattern   : $fingerprint,
            count     : 30,
            timeSpanMs: 100.0,
        );

        $this->assertTrue($report->isSevere(threshold: 20));
        $this->assertFalse($report->isSevere(threshold: 50));
    }

    public function test_n_plus_one_report_summary() : void
    {
        $fingerprint = QueryFingerprint::fromQuery('SELECT * FROM users WHERE id = ?');
        $report = new NPlusOneQueryReport(
            pattern   : $fingerprint,
            count     : 25,
            timeSpanMs: 150.0,
            suggestion: 'Use eager loading',
        );

        $summary = $report->summary();

        $this->assertStringContainsString('N+1 Query Detected', $summary);
        $this->assertStringContainsString('25', $summary);
        $this->assertStringContainsString('150', $summary);
        $this->assertStringContainsString('Use eager loading', $summary);
    }

    public function test_n_plus_one_report_with_suggestion() : void
    {
        $fingerprint = QueryFingerprint::fromQuery('SELECT * FROM users WHERE id = ?');
        $report = new NPlusOneQueryReport(
            pattern   : $fingerprint,
            count     : 10,
            timeSpanMs: 50.0,
        );

        $newReport = $report->withSuggestion('New suggestion');

        $this->assertNull($report->suggestion());
        $this->assertSame('New suggestion', $newReport->suggestion());
    }

    // ==================== PersistenceTimeline Tests ====================

    public function test_persistence_timeline_record() : void
    {
        $timeline = new PersistenceTimeline();
        $timeline->record('SELECT * FROM users', 10.5);

        $this->assertSame(1, $timeline->getQueryCount());
    }

    public function test_persistence_timeline_get_timeline() : void
    {
        $timeline = new PersistenceTimeline();
        $timeline->record('SELECT * FROM users', 10.5);
        $timeline->record('SELECT * FROM posts', 20.3);

        $entries = $timeline->getTimeline();

        $this->assertCount(2, $entries);
        $this->assertSame('SELECT * FROM users', $entries[0]['query']);
        $this->assertSame('SELECT * FROM posts', $entries[1]['query']);
    }

    public function test_persistence_timeline_total_time() : void
    {
        $timeline = new PersistenceTimeline();
        $timeline->record('SELECT * FROM users', 10.0);
        $timeline->record('SELECT * FROM posts', 20.0);
        $timeline->record('SELECT * FROM comments', 5.0);

        $this->assertSame(35.0, $timeline->getTotalTime());
    }

    public function test_persistence_timeline_average_time() : void
    {
        $timeline = new PersistenceTimeline();
        $timeline->record('SELECT * FROM users', 10.0);
        $timeline->record('SELECT * FROM posts', 20.0);

        $this->assertSame(15.0, $timeline->getAverageTime());
    }

    public function test_persistence_timeline_average_time_empty() : void
    {
        $timeline = new PersistenceTimeline();
        $this->assertSame(0.0, $timeline->getAverageTime());
    }

    public function test_persistence_timeline_slowest_query() : void
    {
        $timeline = new PersistenceTimeline();
        $timeline->record('SELECT * FROM users', 10.0);
        $timeline->record('SELECT * FROM posts', 50.0);
        $timeline->record('SELECT * FROM comments', 20.0);

        $slowest = $timeline->getSlowestQuery();

        $this->assertSame('SELECT * FROM posts', $slowest['query']);
        $this->assertSame(50.0, $slowest['duration']);
    }

    public function test_persistence_timeline_slowest_query_empty() : void
    {
        $timeline = new PersistenceTimeline();
        $this->assertNull($timeline->getSlowestQuery());
    }

    public function test_persistence_timeline_get_by_fingerprint() : void
    {
        $timeline = new PersistenceTimeline();
        $timeline->record('SELECT * FROM users WHERE id = 1', 10.0);
        $timeline->record('SELECT * FROM users WHERE id = 2', 12.0);
        $timeline->record('SELECT * FROM posts', 5.0);

        // Get the fingerprint from the first recorded entry
        $entries = $timeline->getTimeline();
        $fingerprint = $entries[0]['fingerprint'];
        $matches = $timeline->getByFingerprint($fingerprint);

        $this->assertCount(2, $matches);
    }

    public function test_persistence_timeline_slow_queries() : void
    {
        $timeline = new PersistenceTimeline();
        $timeline->record('SELECT * FROM users', 10.0);
        $timeline->record('SELECT * FROM posts', 100.0);
        $timeline->record('SELECT * FROM comments', 50.0);

        $slowQueries = $timeline->getSlowQueries(thresholdMs: 40.0);

        $this->assertCount(2, $slowQueries);
    }

    public function test_persistence_timeline_time_span() : void
    {
        $timeline = new PersistenceTimeline();
        $timeline->record('SELECT * FROM users', 10.0, timestamp: 1000.0);
        $timeline->record('SELECT * FROM posts', 20.0, timestamp: 2000.0);

        $this->assertSame(1000.0, $timeline->getTimeSpan());
    }

    public function test_persistence_timeline_time_span_empty() : void
    {
        $timeline = new PersistenceTimeline();
        $this->assertSame(0.0, $timeline->getTimeSpan());
    }

    public function test_persistence_timeline_reset() : void
    {
        $timeline = new PersistenceTimeline();
        $timeline->record('SELECT * FROM users', 10.0);
        $timeline->reset();

        $this->assertSame(0, $timeline->getQueryCount());
        $this->assertSame(0.0, $timeline->getTotalTime());
    }

    public function test_persistence_timeline_summary() : void
    {
        $timeline = new PersistenceTimeline();
        $timeline->record('SELECT * FROM users', 10.0);
        $timeline->record('SELECT * FROM posts', 20.0);

        $summary = $timeline->summary();

        $this->assertSame(2, $summary['count']);
        $this->assertSame(30.0, $summary['totalTime']);
        $this->assertSame(15.0, $summary['averageTime']);
        $this->assertNotNull($summary['slowestQuery']);
    }
}
