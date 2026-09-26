
## Guardrails — do not let a regeneration re-break CI

This repo's CI runs gates the generator's own petstore build does not: dependency
hygiene, analyzers, type checks. After `make generate`, run this repo's real CI gates
locally (not just `make test`) before pushing. Specifically:

- **Declare only the dependencies THIS repo's emitted tests actually use.** Do NOT add
  dependencies to match the generator's `.openapi-generator/DEV-DEPENDENCIES` file: that
  file lists deps for the generator's full petstore test suite, most of which this repo
  does not receive. An unused declared dependency fails the dependency-analysis gate; a
  used-but-undeclared one fails it too. Check with the repo's own dependency gate.
- **Fix a failing gate in the generator, not by hand-editing generated files** — a hand
  edit is lost on the next regeneration. Only keep-listed (hand-written) files are safe
  to edit here.
- **A green `make test` is not a green CI.** The tests can pass while a lint/analyze/
  type gate fails; verify each gate this repo's CI defines.
- **A green local test run is not a green CI, either — Docker Desktop is lenient.**
  The squid proxy fixture (`tests/ZitadelTest.php`, `spec/Setup.php`) publishes two
  ports, 3128 and 3129. testcontainers-php sets host `PortBindings` but never
  `Config.ExposedPorts`, so the daemon only publishes ports the image already `EXPOSE`s.
  `ubuntu/squid` exposes 3128 but not 3129, so on CI's strict daemon the 3129 binding is
  dropped and `getMappedPort(3129)` returns empty; local Docker Desktop publishes it
  anyway and hides the bug. The fixture's proxy container therefore extends
  `GenericContainer` to (a) mount `/var/log/squid` + `/var/spool/squid` as tmpfs so squid
  can boot, and (b) override `createContainerConfig()` to set `ExposedPorts` for every
  requested port. Do not "simplify" either away, and if you add a published port, expose
  it there too. An empty `ExposedPorts` item serialises to `[]`; the daemon needs `{}`,
  so each item carries one ignored entry to force object serialisation.
