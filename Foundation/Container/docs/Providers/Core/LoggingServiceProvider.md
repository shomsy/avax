# 🩺 LoggingServiceProvider

>
> **The Eyes and Ears of your Application's Birth.**

## 🌟 Quick Summary

The `LoggingServiceProvider` is responsible for setting up the infrastructure that allows you to "see" what's happening
during the most critical phase of your app: **The Bootstrap**. It registers the `LoggerFactory`, binds a default
`LoggerInterface`, and installs the global `ErrorHandler`.

### For Humans: Purpose

It’s like the first thing a medic does on a scene: establish communication (logging) and check the vitals (error
handling). Without this provider, if your app crashes during setup, you're left in total darkness. With it, you get a
detailed "black box" recording of exactly what went wrong.

---

## 🧠 Mental Model: "Wait for it..."

This provider operates in two distinct phases:

1. **Preparation (Register)**: It tells the container *how* to build loggers.
2. **Activation (Boot)**: It flips the switch and starts recording.

### For Humans: The Analogy

Registration is like buying the cameras and monitors. Booting is when you actually mount them on the walls and turn them
on.

---

## 🛠️ The Mechanics

| Skill               | Mental Model         | Role                                                         |
|:--------------------|:---------------------|:-------------------------------------------------------------|
| **LoggerFactory**   | The Factory          | Creates consistent loggers for any channel (DB, Auth, etc.). |
| **LoggerInterface** | The Microphone       | The tool you use to talk to the log files.                   |
| **ErrorHandler**    | The Safety Perimeter | Catches every slip-up and records it before the app dies.    |

---

## 🏗️ Technical Flow

1. **`register()`**: We bind `LoggerFactory` as a singleton. We also tell the container that whenever someone asks for
   `LoggerInterface`, it should use the factory to create a logger for the `bootstrap-error-logs` channel.
2. **`boot()`**: This is the "Turn On" phase. We grab the `ErrorHandler` from the container and call `initialize()`.
   This installs the global PHP error, exception, and shutdown handlers.

---

## 📖 Related Files & Folders

- **`Avax\Logging\LoggerFactory`**: The master creator of loggers.
- **`Avax\Logging\ErrorHandler`**: The sentinel that catches global failures.
- **`docs/Observe/`**: To learn more about how we use these logs for observability.

---
> "The only thing worse than a bug is a bug you can't see." — Logging is your superpower.
