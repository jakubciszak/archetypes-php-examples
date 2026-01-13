# GitHub Actions CI Workflows

This repository uses GitHub Actions for continuous integration across multiple archetype contexts.

## CI Workflow (`ci.yml`)

The main CI workflow runs automated tests and code quality checks for all archetype implementations.

### Pipeline Steps

The pipeline executes the following steps **in order** for each context:

1. **PHPStan** - Static analysis at maximum level
2. **Deptrac** - Architecture layer dependency analysis
3. **PHPUnit (Unit Tests)** - Fast unit tests for domain logic
4. **PHPUnit (Integration Tests)** - Integration tests with full setup

### Matrix Strategy

The workflow uses a matrix strategy to run tests in parallel for multiple contexts:

```yaml
matrix:
  php: ['8.4']
  context:
    - name: 'Availability/Simple'
      path: 'availability/simple-availability'
    # Add more contexts here as new archetypes are added
```

### Adding New Contexts

To add a new archetype context to CI:

1. Add a new entry to the `matrix.context` array in `.github/workflows/ci.yml`:

```yaml
- name: 'YourArchetype/Pattern'
  path: 'your-archetype/pattern-name'
```

2. Ensure your archetype has:
   - `composer.json` with required dev dependencies
   - `phpunit.xml` configured with `unit` and `integration` test suites
   - `phpstan.neon` configuration
   - `deptrac.yaml` configuration

### Running CI Locally

Each archetype includes composer scripts for running CI steps locally:

```bash
cd availability/simple-availability

# Run full CI pipeline
composer ci

# Run individual steps
composer phpstan              # Static analysis
composer deptrac              # Architecture analysis
composer test:unit            # Unit tests only
composer test:integration     # Integration tests only
composer test                 # All tests
```

### Caching

The workflow uses composer dependency caching to speed up builds:
- Cache key includes context name and `composer.lock` hash
- Each context has independent cache to avoid conflicts

### Trigger Conditions

CI runs on:
- Pushes to `main`, `develop` branches
- Pushes to branches matching `claude/**`
- Pull requests targeting `main` or `develop`

### Expected Results

All steps should pass for the build to succeed:
- ✅ PHPStan: No errors at level max
- ✅ Deptrac: No architecture violations
- ✅ Unit Tests: All tests passing
- ✅ Integration Tests: All tests passing
