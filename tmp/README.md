# AvaX Recovery Kit

Safe tooling for AvaX V1 muscle recovery.

Install from AvaX root:

```bash
unzip avax-recovery-kit.zip -d /tmp/avax-recovery-kit
bash /tmp/avax-recovery-kit/install.sh
```

First command:

```bash
make -f Makefile.recovery recovery-database
```

Other commands:

```bash
make -f Makefile.recovery recovery-persistence
make -f Makefile.recovery recovery-all
make -f Makefile.recovery recovery-stamp-missing-v1
make -f Makefile.recovery recovery-proof-guard
```

Rule: if only report/tooling files changed, the stage is AUDIT-ONLY, not RESTORED/COMPLETE.
