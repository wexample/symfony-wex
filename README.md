# symfony_wex

Version: 3.0.3

`wexample/symfony-wex` is a Symfony bundle that integrates `wexample/php-wex` into the Symfony service container, registering `WexClient` as an autowired service so application code can invoke wex CLI commands without managing processes directly. It exposes a `wexample_symfony_wex` configuration block where the wex binary name or path, the working directory commands run from, and an optional timeout can be set. It targets Symfony developers who need programmatic access to the wex CLI from within a Symfony application.

## Table of Contents

- [Architecture](#architecture)
- [Integration in the Suite](#integration-in-the-suite)
- [Dependencies](#dependencies)
- [Versioning & Compatibility Policy](#versioning--compatibility-policy)
- [License](#license)
- [About us](#about-us)
- [Migration Notes](#migration-notes)

## Architecture

`wexample/symfony-wex` is a thin Symfony bundle whose only job is to wire `wexample/php-wex`'s `WexClient` into the Symfony container and expose it through standard bundle configuration. All execution logic lives in `php-wex`; this package owns nothing beyond the Symfony integration layer.

### Entry point

src/WexampleSymfonyWexBundle.php declares the bundle. It extends `AbstractBundle` from `wexample/symfony-helpers`, which handles the convention-based discovery of the extension class, and implements `LoaderBundleInterface` to hand `assets/` to `wexample/symfony-loader` — that registration is what gives the bundle's translations the `WexampleSymfonyWexBundle` prefix, and what lets `AppForm` find its labels and its template under `assets/forms/`.

### DependencyInjection

src/DependencyInjection/Configuration.php defines the bundle's configuration tree under the root key `wexample_symfony_wex`. It exposes three scalar nodes:

- `binary` — name resolved against `PATH`, or an absolute path to a wex executable; defaults to `Globals::CORE_COMMAND_NAME` from `wexample/php-wex`.
- `working_directory` — the directory commands run from, which controls which app's commands are reachable; defaults to `null`, resolved at load time to `kernel.project_dir`.
- `timeout` — seconds before a command is terminated; `null` waits indefinitely.

src/DependencyInjection/WexampleSymfonyWexExtension.php loads the service definition file, processes the configuration through `Configuration`, and writes the three resulting values into container parameters:

```
wexample_symfony_wex.binary
wexample_symfony_wex.working_directory
wexample_symfony_wex.timeout
```

When `working_directory` is absent from the user's config, the extension falls back to `kernel.project_dir` directly:

```php
$config['working_directory'] ?? $container->getParameter('kernel.project_dir')
```

### Service wiring

src/Resources/config/services.yaml does two things:

1. Registers every class under `src/Service/` with autowiring and autoconfiguration.
2. Explicitly registers `Wexample\PhpWex\Common\WexClient`, injecting the three container parameters as constructor arguments (`$binary`, `$workingDirectory`, `$timeout`).

`WexClient` is therefore available for injection into any service or controller in the host application without further setup.

### Call path

At container compile time: extension loads → configuration processed → three parameters written → `WexClient` bound to those parameters.

At runtime: a service in `src/Service/` (or any host-application class) receives `WexClient` by type-hint. Calling a method on it invokes the wex binary at `binary`, from `working_directory`, subject to `timeout`. No Symfony code is on that execution path; the bundle's role ends at wiring.

## Integration in the Suite

This package is part of the Wexample Suite — a collection of high-quality, modular tools designed to work seamlessly together across multiple languages and environments.

### Related Packages

The suite includes packages for configuration management, file handling, prompts, and more. Each package can be used independently or as part of the integrated suite.

Visit the [Wexample Suite documentation](https://docs.wexample.com) for the complete package ecosystem.

## Dependencies

- php: >=8.5
- doctrine/orm: ^3.0
- symfony/http-client: ^7.0
- symfony/uid: ^7.0
- symfony/yaml: ^7.0
- wexample/symfony-api: >=5.0.0
- wexample/symfony-pseudocode: >=3.0.0
- wexample/symfony-forms: >=7.0.0
- wexample/symfony-helpers: >=9.0.0
- wexample/symfony-messenger: >=3.0.0
- wexample/php-pseudocode: >=2.0.0
- wexample/php-wex: >=1.0.0

## Versioning & Compatibility Policy

Wexample packages follow **Semantic Versioning** (SemVer):

- **MAJOR**: Breaking changes
- **MINOR**: New features, backward compatible
- **PATCH**: Bug fixes, backward compatible

We maintain backward compatibility within major versions and provide clear migration guides for breaking changes.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

Free to use in both personal and commercial projects.

## About us

[Wexample](https://wexample.com) stands as a cornerstone of the digital ecosystem — a collective of seasoned engineers, researchers, and creators driven by a relentless pursuit of technological excellence. More than a media platform, it has grown into a vibrant community where innovation meets craftsmanship, and where every line of code reflects a commitment to clarity, durability, and shared intelligence.

This packages suite embodies this spirit. Trusted by professionals and enthusiasts alike, it delivers a consistent, high-quality foundation for modern development — open, elegant, and battle-tested. Its reputation is built on years of collaboration, refinement, and rigorous attention to detail, making it a natural choice for those who demand both robustness and beauty in their tools.

Wexample cultivates a culture of mastery. Each package, each contribution carries the mark of a community that values precision, ethics, and innovation — a community proud to shape the future of digital craftsmanship.

## Migration Notes

When upgrading between major versions, refer to the migration guides in the documentation.

Breaking changes are clearly documented with upgrade paths and examples.
