# DevTools

## container:report

Outputs a JSON bundle with health, runtime summary, bindings, tags, and cache configuration.

```bash
php bin/container container:report
```

## container:hooks

Shows lifecycle hook metrics and execution profile.

```bash
php bin/container container:hooks
```

## container:coverage

Summarizes clover coverage output (default: `build/logs/clover.xml`).

```bash
php bin/container container:coverage build/logs/clover.xml
```

## container:health (ASCII)

Render health metrics as an ASCII chart.

```bash
php bin/container container:health --format=ascii
```
