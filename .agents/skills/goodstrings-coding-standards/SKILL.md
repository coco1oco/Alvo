---
name: goodstrings-coding-standards
description: Enforces GoodStrings Inc. Engineering Team coding standards for PHP, Laravel, JavaScript, Vue.js, CSS, and Git workflows. Use when writing, reviewing, or refactoring code in this project.
---

# GoodStrings Engineering Team Coding Standards

This document serves as the guide for GoodStrings Inc. Engineering Team's codebases, covering code formatting and version control usage to maintain a readable, consistent, high-quality codebase.

---

## General Guidelines

- **Clean & Readable Code**: Prioritize clarity and maintainability.
- **Principles**: Strictly adhere to **DRY** (Don't Repeat Yourself) and **KISS** (Keep It Simple, Stupid).
- **Naming**: Use descriptive and meaningful names for variables, methods, classes, and files.
- **Documentation**: Add comments and docblocks where necessary.
- **Version Control**: Use Git with frequent, meaningful commit messages.

---

## PHP Standards

### General Guidelines
- Use the latest supported PHP version.
- Adhere to **PSR-12** coding style.

### File Conventions
- Use **4 spaces** for indentation (no tabs).
- Files must start with `<?php` and **omit** `?>` at the end of the file.
- All PHP files must declare a `namespace`.
- `namespace` declaration must be written on a new line directly after `<?php`.

### Naming Conventions
- **Classes / Interfaces / Traits / Abstract Classes**: `PascalCase` (e.g., `UserController`).
  - Abstract classes must use the `Abstract` prefix (e.g., `AbstractBaseRepository`).
  - Interfaces must use the `Interface` suffix (e.g., `UserRepositoryInterface`).
  - Traits must use the `Trait` suffix (e.g., `LoggableTrait`).
- **Methods**: `camelCase` (e.g., `getUserData`).
- **Variables**: `camelCase` (e.g., `$userName`).
- **Constants**: `UPPER_SNAKE_CASE` (e.g., `MAX_ATTEMPTS`).

### Class Definitions
- Place a newline before `{` in class definitions.
- `extends` and `implements` must be on the same line as the class name.
- Keywords `abstract`, `final`, or `trait` must come before `class`.

```php
class ClassName extends AnotherClassName implements ClassNameInterface
{
    // Code here...
}

abstract class AbstractClassName
{
    // Code here...
}

interface ClassNameInterface
{
    // Code here...
}

trait SomeTrait
{
    // Code here...
}
```

### Property Definitions
- Never omit `public`, `protected`, or `private` access modifiers (explicitly write `public` when public).
- `static` keyword must come *after* the access modifier (e.g., `protected static $property`).
- Do not use `var` to define properties.
- Do not define multiple properties in a single statement.

```php
class ClassName
{
    public $propertyOne;
    private $propertyTwo;
    protected static $staticProperty;
}
```

### Function & Method Definitions
- Place a newline before `{` in function/method definitions.
- Always specify access modifiers (`public`, `protected`, `private`).
- Keywords `abstract` or `final` must precede access modifiers.
- No spaces inside parameter parentheses: `function example(string $param)` (no space before `(` or after `)`).
- Place whitespaces after commas in arguments, not before.
- **Type Hinting**: Always use type hints on arguments and declare explicit return types when possible.
- **Multi-line Arguments**: When arguments overflow, put a newline after `(`, write one argument per line, and put `) {` on the same line separated by a single space.

```php
class ClassName
{
    abstract protected function doSomething(array $data): array;

    public function somethingWasDone(string $status, int $code): ClassName
    {
        return $this;
    }

    public function tooMany(
        string $name,
        int $status,
        array $otherData
    ) {
        // Code here...
    }
}
```

### Conditional Statements
- Opening `{` stays on the same line (do not put a newline before `{`).
- Place one space before `(` and after `)`. No spaces directly inside `(` or before `)`.
- Comparisons: Always place the check target on the **right** side (e.g., `if ($condition == null)`).

```php
if ($condition == true) {
    // Code here...
}

if (
    $condition == true &&
    ($condition1 == $condition2) ||
    $condition3 != 'some condition here'
) {
    // Code here...
}
```

### Comments & PHPDoc
- Single-line and multi-line doc comments must start with `/**` and end with `*/`.
- Always include a `@var` comment on class variables.
- Refrain from commenting functions if the name is self-explanatory, **except** for functions in Repository Interfaces (which must always document their purpose).
- Mandatory tags: `@var`, `@param` (must include a short description), `@return`.

```php
class ClassName
{
    /** @var VariableType */
    public $classVariable;

    /**
     * Explains what the function does.
     *
     * @param  Model $id the user's unique id
     * @param  array $otherData user's other data
     * @return array
     */
    public function someFunction(Model $id, array $otherData): array
    {
        return $arrayType;
    }
}
```

---

## Laravel Standards

### Folder Structure & Architecture
- Follow Laravel's default directory structure.
- Place business logic inside **Service** or **Repository** classes, never directly in Controllers.

### Naming Conventions
- **Controllers**: Singular and descriptive (e.g., `UserController`).
- **Models**: Singular (e.g., `User`).
- **Routes**: Use `snake_case` for named routes (e.g., `get_user_profile`).

### Best Practices
- **Eloquent ORM**: Use Eloquent for all database interactions.
- **Form Requests**: Use custom Form Request classes for validation to keep controllers thin.
- **Relationships & Scopes**: Use Eloquent relationships and query scopes to simplify queries.
- **Transactions**: Wrap multi-table mutation processes (create, update, delete) in `DB::transaction()`.
- **Queues & Jobs**: Offload heavy operations to Laravel events, queues, and background jobs.
- **Blade**: Avoid logic in Blade templates; compute state in controllers or view composers.
- **Configuration**: Use `env()` via `config()` helpers (e.g., `config('app.timezone')`), never hardcode sensitive values or use raw `env()` outside config files.

### Database & Security
- Use migrations for all schema changes.
- Use seeders and factories for initial and testing data.
- Protect forms with CSRF middleware (`VerifyCsrfToken`).
- Use `$request->input()` or `$request->get()` instead of global superglobals (`$_POST`, `$_GET`).
- Prevent mass assignment by explicitly defining `$fillable` or `$guarded` in models.
- Always hash passwords with the `Hash` facade.

---

## JavaScript & NPM Standards

### General & Style
- Use **2 spaces** for indentation.
- `import` statements must always be at the **top** of the file.
- `export` statements must always be at the **bottom** of the file.
- **Omit semicolons (`;`)** at the end of statements.
- Refer to the **Airbnb JavaScript Style Guide** for rules not covered here.

### Variables & Functions
- **Variables & Functions**: `camelCase` (e.g., `fetchData`).
- **Constants**: `UPPER_SNAKE_CASE` (e.g., `API_BASE_URL`).
- Never use `var`; use `const` (preferred) or `let`.

### Best Practices & Security
- Use `async`/`await` for asynchronous operations.
- Prefer array methods (`.map()`, `.filter()`, `.reduce()`) over traditional loops.
- Avoid `eval()` or dynamically generated code.
- Validate and sanitize input to prevent XSS.

### NPM Dependency Management
- Third-party packages must use **fixed version numbers** in `package.json` (no `^` or `~` prefixes) to prevent unexpected breaking changes.

```json
{
    "dependencies": {
        "npm-package": "1.10.11"
    }
}
```

---

## Vue.js Standards

### File & Component Structure
- Organize files by feature/module. Limit folder nesting for simplicity.
- Component File Names: `PascalCase` (e.g., `UserProfile.vue`).
- Component Template Names: `PascalCase` (e.g., `<UserProfile />`).
- Component Props: `camelCase` (e.g., `userName`).
- Single File Component order: `<template>` -> `<script>` -> `<style scoped>`.

### Best Practices
- Use Vuex / Pinia for central state management in large apps.
- Use `props` to pass data down to child components and `emit` for parent communication.
- Keep components small and modular.
- Always provide explicit `:key` attributes on lists.
- Validate props with explicit types and default values.

---

## CSS Standards

- Adhere to the **Airbnb CSS Style Guide**.
- Avoid inline styles; use scoped CSS for component-specific styling.

---

## Git Workflow & Branching

### Branching Model (Gitflow Workflow)
- **`main` / `master`**: Production-ready code; protected branch.
- **`develop`**: Complete integration history; parent branch for all feature branches.
- **`feature/`**: Feature development (e.g., `feature/user-auth`). Merges back into `develop`.
- **`hotfix/`**: Critical production bug fixes branching from `main`. Merges into `main` and `develop`.
- **`release/`**: Preparation for release from `develop` (e.g., `release/beta_v1.0.1`). Merges into `main` and `develop`.
- Delete `feature`, `hotfix`, and `release` branches immediately after merging.

### Versioning & Tagging
- Release versions use prefix `v` (e.g., `v1.0.1`).
- Pre-releases use prefixes `alpha_` or `beta_` (e.g., `release/beta_v1.0.1`).

### Commits & Pull Requests
- Commit small, commit often. Do not commit broken or half-done work.
- Keep commit messages concise and explanatory.
- Update local branches and resolve merge conflicts prior to opening a PR.
- All PRs require review by the Engineering Lead or CTO before merging.

---

## Linter & Tooling Notes

- **PHP Code Styling**: Run Laravel Pint or PHP CS Fixer configured for PSR-12 and 4-space indentation.
- **JavaScript Linting**: Use ESLint with rules enforcing no semicolons (`semi: ["error", "never"]`) and 2-space indentation.
- **Git Hooks**: Recommended to set up pre-commit hooks (Husky / Git hooks) to run linter checks before committing.
