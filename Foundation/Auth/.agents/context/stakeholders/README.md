# Stakeholders

## Primary Stakeholders

### 1. Framework Developers
- **Role**: Build and maintain Avax Auth
- **Needs**: Clear architecture, good DX, testable code
- **Influence**: Architecture decisions, API design

### 2. Application Developers (Users)
- **Role**: Use Avax Auth in their projects
- **Needs**: Easy integration, clear docs, flexibility
- **Influence**: Feature requests, API feedback

### 3. End Users
- **Role**: Use applications built with Avax Auth
- **Needs**: Secure login, privacy, account safety
- **Influence**: Security requirements

## Stakeholder Map

```
Framework Developers
        ↓ (builds)
    Avax Auth
        ↓ (used by)
Application Developers
        ↓ (builds apps for)
    End Users
```

## Communication

| Stakeholder | Channel | Frequency |
|-------------|---------|-----------|
| Framework Developers | GitHub issues, PRs | Daily |
| Application Developers | GitHub, docs | As needed |
| End Users | (Via app developers) | N/A |

## Interests to Balance

1. **Simplicity vs Flexibility** — Easy to use, but extensible
2. **Security vs UX** — Secure by default, but not annoying
3. **Features vs Maintenance** — Useful features, but maintainable