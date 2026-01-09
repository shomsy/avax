# Telemetry

## Quick Summary
Telemetry provides a comprehensive observability interface for container operations, enabling collection, export, and monitoring of performance metrics and health data. It serves as the primary API for accessing container telemetry, offering both human-readable exports and programmatic access to operational data. This class bridges the gap between raw metrics collection and usable observability features for monitoring and debugging.

### For Humans: What This Means
Imagine Telemetry as the control panel of your car's dashboard—the central display that takes all the engine sensors and presents them in a way you can understand and act upon. Instead of raw sensor data, you get clear readouts, warning lights, and exportable reports. For containers, Telemetry takes complex internal metrics and turns them into actionable information for monitoring, debugging, and optimization.

## Terminology (MANDATORY, EXPANSIVE)
**Container Telemetry**: Operational data collected from container activities including performance metrics, health status, and usage statistics. In this file, this is the core data being managed. It matters because it enables operational visibility and optimization.

**Metrics Export**: The process of formatting collected metrics into external formats like JSON for monitoring systems. In this file, exportMetrics() provides this functionality. It matters because it enables integration with external monitoring tools.

**Health Status**: A standardized assessment of system operational status including availability, performance, and error conditions. In this file, getHealthStatus() provides this assessment. It matters because it enables automated monitoring and alerting.

**Observability Interface**: A programmatic API for accessing system telemetry and monitoring data. In this file, this is the interface provided by the class. It matters because it enables applications to build monitoring and debugging tools.

**Metrics Collection**: The gathering and aggregation of performance and operational data from system components. In this file, CollectMetrics handles this underlying collection. It matters because it provides the raw data foundation for telemetry.

### For Humans: What This Means
These are the telemetry vocabulary. Container telemetry is the vital signs. Metrics export is downloading the medical records. Health status is the doctor's assessment. Observability interface is the patient portal. Metrics collection is taking the measurements.

## Think of It
Picture a sophisticated medical monitoring system in a hospital room—heart rate monitors, blood pressure sensors, oxygen levels, all feeding into a central display that nurses and doctors can read at a glance. Telemetry is that central monitoring display for your container—the system that takes all the internal sensors and presents them as clear, actionable information. It transforms raw data into insights that help you keep your container healthy and performing well.

### For Humans: What This Means
This analogy shows why Telemetry exists: human-friendly monitoring. Without it, metrics data would remain raw numbers in logs, useless for quick assessment or automated monitoring. Telemetry creates the dashboard that makes container health immediately understandable and actionable.

## Story Example
Before Telemetry existed, monitoring container performance required manual log analysis and custom instrumentation. Developers had to write custom code to collect metrics, format them for external systems, and assess container health. Metrics were scattered across different systems and hard to correlate. With Telemetry, container observability became standardized. A single API provided access to all metrics, health status could be checked programmatically, and data could be exported to monitoring systems automatically. Container monitoring became systematic rather than ad-hoc.

### For Humans: What This Means
This story illustrates the monitoring complexity Telemetry solves: scattered, inconsistent observability. Without it, understanding container performance was like trying to monitor a patient's health from disconnected vital sign readings. Telemetry creates the unified monitoring system that makes container health clear and manageable.

## For Dummies
Let's break this down like a smart home hub:

1. **The Problem**: Container metrics are scattered and hard to access for monitoring and debugging.

2. **Telemetry's Job**: It's the smart home hub that collects data from all sensors and presents it clearly.

3. **How You Use It**: Call methods to get metrics, health status, or export data for monitoring systems.

4. **What Happens Inside**: Collects metrics from the container, formats them for different uses, provides health assessments.

5. **Why It's Helpful**: You get unified access to all container monitoring data in usable formats.

Common misconceptions:
- "Telemetry is just logging" - It provides structured, queryable operational data.
- "Telemetry slows down the container" - Monitoring is designed to be lightweight.
- "Telemetry is only for production" - It's essential for development, testing, and production.

### For Humans: What This Means
Telemetry isn't just data collection—it's operational intelligence. It takes the chaos of container monitoring and creates systematic, accessible insights. You get professional observability without becoming a monitoring expert.

## How It Works (Technical)
Telemetry acts as a facade over the CollectMetrics system, providing high-level methods for accessing and exporting telemetry data. It formats raw metrics into structured outputs, handles JSON serialization, and provides health status assessments based on collected data.

### For Humans: What This Means
Under the hood, it's like a translator and formatter. It takes raw metrics from the collection system, translates them into usable formats, adds timestamps and structure, and presents them through a clean API. It's the bridge between internal data collection and external consumption.

## Architecture Role
Telemetry sits at the observability interface layer, providing the public API for container monitoring while delegating data collection to specialized systems. It enables comprehensive container observability without exposing internal monitoring implementation details.

### For Humans: What This Means
In the container's architecture, Telemetry is the public interface—the welcome desk that provides access to all monitoring information. It presents a clean API while the complex collection and analysis happens behind the scenes.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(ContainerInterface $container, CollectMetrics $metrics): void

#### Technical Explanation
Creates a new Telemetry instance with dependencies for container access and metrics collection.

##### For Humans: What This Means
This sets up the telemetry system by connecting it to the container and the metrics collector. It's like plugging in the monitoring sensors to start collecting data.

##### Parameters
- `ContainerInterface $container`: The container instance to monitor.
- `CollectMetrics $metrics`: The metrics collector that gathers telemetry data.

##### Returns
- `void`: Constructor doesn't return anything; it just initializes the telemetry system.

##### Throws
- None. Constructor only stores dependencies.

##### When to Use It
- When creating telemetry monitoring for a container instance.
- In monitoring setup or container initialization.
- When implementing container observability features.

##### Common Mistakes
- Passing null or invalid container/metrics instances.
- Not properly configuring the metrics collector before creating telemetry.

### Method: exportMetrics(): string

#### Technical Explanation
Collects all available metrics and exports them as a formatted JSON string with timestamp, suitable for external monitoring systems and logging.

##### For Humans: What This Means
This gives you all the container's performance and operational data in a format that external monitoring systems can understand and store. It's like exporting your medical records for a specialist to review.

##### Parameters
- None.

##### Returns
- `string`: JSON-formatted metrics data with timestamp and structured metrics.

##### Throws
- `RuntimeException`: If JSON encoding fails (rare, but possible with malformed data).

##### When to Use It
- When sending metrics to external monitoring systems.
- For logging comprehensive container state.
- When implementing automated monitoring and alerting.

##### Common Mistakes
- Not handling the RuntimeException for JSON encoding failures.
- Using exportMetrics() in performance-critical code (it's meant for occasional exports).
- Assuming the JSON format is stable for parsing (use getMetrics() for programmatic access).

### Method: getMetrics(): array

#### Technical Explanation
Returns raw metrics data as an array for programmatic access and internal analysis.

##### For Humans: What This Means
This gives you direct access to the metrics data as a PHP array, perfect for writing your own analysis or monitoring logic within the application.

##### Parameters
- None.

##### Returns
- `array`: Raw metrics data organized by metric type and values.

##### Throws
- None. Metrics access is designed to be safe.

##### When to Use It
- When building custom monitoring or analysis logic.
- For programmatic metrics processing within the application.
- When you need raw metrics data for calculations or decisions.

##### Common Mistakes
- Assuming specific metric structure without checking documentation.
- Using getMetrics() for external system integration (use exportMetrics()).
- Not caching results if called frequently.

### Method: getHealthStatus(): array

#### Technical Explanation
Provides a standardized health check response with operational status, metrics summary, and timestamp information.

##### For Humans: What This Means
This gives you a quick health check of the container—like a doctor's summary saying "everything looks good" with some basic stats. It's perfect for monitoring systems that need to know if the container is running properly.

##### Parameters
- None.

##### Returns
- `array`: Health status data including operational status, timestamp, and metrics summary.

##### Throws
- None. Health checks are designed to be safe.

##### When to Use It
- For container health monitoring and alerting.
- In load balancer health checks.
- When implementing operational dashboards.

##### Common Mistakes
- Assuming the status is always 'healthy' (it could change in future versions).
- Using getHealthStatus() for detailed metrics (use getMetrics() instead).
- Not considering the timestamp for staleness checks.

## Risks, Trade-offs & Recommended Practices
**Risk**: Metrics collection can impact performance in high-throughput scenarios.

**Why it matters**: Every metric collection adds some overhead, especially with many metrics.

**Design stance**: Make metrics collection configurable and disable in performance-critical code paths.

**Recommended practice**: Use sampling or conditional metrics collection for high-performance applications.

**Risk**: Exported JSON can become large with many metrics.

**Why it matters**: Large exports can impact memory usage and network transfer.

**Design stance**: Implement pagination or filtering for large metric sets.

**Recommended practice**: Use getMetrics() for internal processing and selective exportMetrics() for external systems.

**Risk**: Health status provides basic assessment only.

**Why it matters**: Complex health issues might not be detected by simple status checks.

**Design stance**: Use health status as a quick check, detailed metrics for comprehensive monitoring.

**Recommended practice**: Combine health status with threshold-based alerting on specific metrics.

### For Humans: What This Means
Telemetry provides powerful monitoring capabilities but requires thoughtful use. The performance impact is usually minimal, but it's important to consider how metrics are collected and exported in different scenarios. The key is using the right method for the right purpose.

## Related Files & Folders
**CollectMetrics**: Provides the underlying metrics collection that Telemetry accesses. You configure metrics collection through this system. It supplies the raw data that Telemetry formats and exports.

**Observe/**: Contains the broader observability system that Telemetry is part of. You encounter telemetry as part of comprehensive container monitoring. It provides the monitoring context for telemetry operations.

**ContainerInterface**: Defines the container that Telemetry monitors. You pass the container to telemetry for monitoring. It provides the system being observed.

### For Humans: What This Means
Telemetry works with a complete monitoring ecosystem. CollectMetrics provides data, Observe gives context, ContainerInterface is the subject. Together they create comprehensive container observability.