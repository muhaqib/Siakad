# Contributing to SIAKAD

First off, thank you for considering contributing to SIAKAD! It's people like you that make SIAKAD such a great tool.

## Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the issue list as you might find out that you don't need to create one. When you are creating a bug report, please include as many details as possible:

- **Use a clear and descriptive title** for the issue to identify the problem.
- **Describe the exact steps which reproduce the problem** in as many details as possible.
- **Provide specific examples to demonstrate the steps**.
- **Describe the behavior you observed after following the steps** and point out what exactly is the problem with that behavior.
- **Explain which behavior you expected to see instead and why.**
- **Include screenshots and animated GIFs** if possible.

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion, please include:

- **Use a clear and descriptive title** for the issue to identify the suggestion.
- **Provide a step-by-step description of the suggested enhancement** in as many details as possible.
- **Provide specific examples to demonstrate the steps**.
- **Describe the current behavior** and **explain which behavior you expected to see instead** and why.
- **Explain why this enhancement would be useful** to most SIAKAD users.

### Pull Requests

1. Fork the repo and create your branch from `main`.
2. If you've added code that should be tested, add tests.
3. If you've changed APIs, update the documentation.
4. Ensure the test suite passes.
5. Make sure your code lints.
6. Issue that pull request!

## Development Setup

### Prerequisites

- PHP 8.2+
- Composer 2.x
- Node.js 18+ & npm
- MySQL 8.0+ / PostgreSQL 14+ / SQLite

### Setup

```bash
# Clone your fork
git clone https://github.com/YOUR_USERNAME/siakad.git
cd siakad

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations with seeders
php artisan migrate:fresh --seed

# Start development server
composer dev
```

## Coding Standards

### PHP

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards
- Use Laravel Pint for code formatting: `./vendor/bin/pint`
- Write meaningful variable and function names
- Add PHPDoc comments for complex methods

### JavaScript

- Use ES6+ syntax
- Follow Alpine.js conventions for interactivity

### CSS

- Use Tailwind CSS utility classes
- Avoid custom CSS when possible
- Follow responsive-first approach

### Git Commit Messages

- Use the present tense ("Add feature" not "Added feature")
- Use the imperative mood ("Move cursor to..." not "Moves cursor to...")
- Limit the first line to 72 characters or less
- Reference issues and pull requests liberally after the first line

Examples:
```
feat: add KRS bulk approval feature
fix: resolve N+1 query in kelas list
docs: update README with installation steps
refactor: optimize status count queries
test: add unit tests for KrsService
```

## Project Structure

```
app/
├── Console/Commands/      # Custom Artisan commands
├── DTOs/                  # Data Transfer Objects
├── Exceptions/            # Custom exception classes
├── Helpers/               # Helper classes
├── Http/
│   ├── Controllers/       # HTTP Controllers
│   ├── Middleware/        # Custom middleware
│   └── Requests/          # Form requests (validation)
├── Models/                # Eloquent models
└── Services/              # Business logic services
```

### Key Conventions

1. **Controllers** - Thin controllers, business logic goes to Services
2. **Services** - Business logic layer, injectable via constructor
3. **Models** - Eloquent models with relationships and scopes
4. **DTOs** - Data Transfer Objects for complex data structures
5. **Exceptions** - Custom exceptions for business logic errors

## Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test --filter=KrsServiceTest

# Run with coverage
php artisan test --coverage
```

## Questions?

Feel free to open an issue with your question or reach out to the maintainers.

---

Thank you for contributing! 🎉
