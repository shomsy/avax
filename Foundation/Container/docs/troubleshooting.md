# 🩺 Troubleshooting Matrix
>
> **"Everything that can go wrong, will—here's how to fix it."**

When the Container fails, it usually happens at a specific "Station" in the [Resolution Flow](./concepts/resolution-flow.md). Use this matrix to map your error to a solution.

---

## 🗺️ The Error Map

| Exception | Stage | Most Likely Cause | ✅ How to Fix |
| :--- | :--- | :--- | :--- |
| `ServiceNotFoundException` | **Gatekeeper** | You asked for a class that doesn't exist or isn't autowirable. | Check the class name or [register it manually](./index.md). |
| `RecursionException` | **Loop Detector** | Class A needs B, and B needs A. | Use **Lazy Loading** or reconsider your architecture. |
| `ResolutionException` | **The Birth** | The constructor has a parameter that can't be resolved (e.g., an `int` without a default). | Add a default value or provide the parameter via [manual binding](./Container.md#make). |
| `ResolutionException` | **The Wiring** | A property marked with `#[Inject]` has a type that doesn't exist. | Ensure the injected class is registered and autowired. |
| `SecurityException` | **Gatekeeper** | A [Guard](./Guard/index.md) prevented access to this service. | Check your environment permissions or firewall rules. |

---

## 🛠️ The "Black Box" Strategy

If the error isn't clear, use the **Black Box approach**:

1. **Check Telemetry**: Look at the output of `exportMetrics()`. It shows the resolution depth.
2. **Inspect Injection**: Use `$container->inspectInjection($obj)` to see what the container thinks it should be doing.
3. **Verify Context**: If using complex resolution, check the `KernelContext` state.

---
> **"A doctor's best tool is the X-ray; a developer's best tool is the Telemetry."** 🔭
