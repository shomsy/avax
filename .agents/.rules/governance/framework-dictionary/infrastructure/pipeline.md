# Term
Pipeline

# Classification
Infrastructure — Data Flow Pattern

# Purpose
A ordered sequence of processing stages where the output of each stage becomes the input of the next, enabling complex transformations to be composed from simple, single-purpose steps.

# Why Allowed
The pipeline pattern is ubiquitous in infrastructure and software engineering. Unix pipes compose simple commands into complex data processing chains. CI/CD pipelines (GitHub Actions, GitLab CI, Jenkins) sequence build, test, and deployment stages. Data engineering pipelines (Apache Airflow, dbt, Spark) transform raw data into analytics-ready formats. Build tool pipelines (PostCSS, Babel, webpack loader chains) transform source code through sequential stages. Log processing pipelines (Fluentd, Logstash, Vector) ingest, parse, filter, and route log events. A pipeline has a clear structure: it defines an ordered flow of stages, each stage has a single responsibility, data flows unidirectionally (or with defined feedback loops), and failures in any stage can halt or redirect the entire flow. It is not a DAG (which allows arbitrary dependency graphs) or a simple function call chain.

# Allowed Contexts
- CI/CD pipelines (GitHub Actions, GitLab CI, Jenkins, Azure Pipelines)
- Data transformation pipelines (Apache Airflow, dbt, Apache Spark, Pandas pipelines)
- Build tool pipelines (PostCSS plugin chain, Babel plugin pipeline, webpack loader chain)
- Log processing pipelines (Fluentd, Logstash, Vector, OpenTelemetry Collector)
- ETL and data engineering pipelines (AWS Glue, Apache Beam, Flink)
- HTTP middleware pipelines (Express, Fastify, ASP.NET Core middleware pipeline)
- Stream processing pipelines (Kafka Streams, Apache Flink, Reactor)

# Forbidden Misuse
- Naming a single function with multiple sequential operations a "pipeline" when it has no composable stages
- Creating a Pipelines/ folder for arbitrary utility scripts that do not form a data flow
- Calling a nested function call chain a "pipeline" when stages are not independently configurable or replaceable
- Using "pipeline" to describe a simple array.map().filter() chain when no stage abstraction exists

# Ecosystem References
- https://docs.github.com/en/actions/using-workflows
- https://docs.gitlab.com/ee/ci/pipelines/
- https://airflow.apache.org/
- https://postcss.org/
- https://www.elastic.co/logstash
- https://kafka.apache.org/documentation/streams/

# Allowed Patterns
- ciBuildPipeline
- dataTransformPipeline
- logProcessingPipeline
- cssTransformPipeline
- etlIngestionPipeline
- httpMiddlewarePipeline

# Forbidden Patterns
- Pipeline (as a folder name)
- PipelineManager
- DataPipeline (too vague — should specify what data is being processed)
- PipelineHelper
- GenericPipeline
