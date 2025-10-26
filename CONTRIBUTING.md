# Contributing to Blockchain Application

Thank you for considering contributing to this blockchain project!

## Development Setup

1. Fork the repository
2. Clone your fork:
   ```bash
   git clone https://github.com/YOUR_USERNAME/blockchain.git
   cd blockchain
   ```

3. Run setup:
   ```bash
   # Windows
   .\setup.bat
   
   # Linux/Mac
   chmod +x setup.sh && ./setup.sh
   ```

## Making Changes

1. Create a new branch:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. Make your changes
3. Test thoroughly:
   - Create transactions
   - Mine blocks
   - Validate blockchain
   - Check all API endpoints

4. Commit your changes:
   ```bash
   git add .
   git commit -m "Add: description of your changes"
   ```

5. Push to your fork:
   ```bash
   git push origin feature/your-feature-name
   ```

6. Create a Pull Request

## Commit Message Convention

Use clear, descriptive commit messages:

- `Add:` for new features
- `Fix:` for bug fixes
- `Update:` for updates to existing features
- `Refactor:` for code refactoring
- `Docs:` for documentation changes
- `Style:` for formatting changes
- `Test:` for adding tests

Examples:
```
Add: user authentication middleware
Fix: blockchain validation error on genesis block
Update: proof of work difficulty configuration
Docs: add API endpoint documentation
```

## Code Style

### Backend (Laravel)
- Follow PSR-12 coding standard
- Use meaningful variable and function names
- Add PHPDoc comments for classes and methods
- Keep controllers thin, services fat

### Frontend (React)
- Use functional components with hooks
- Keep components small and focused
- Use meaningful component and variable names
- Add PropTypes or TypeScript for type checking

## Testing

Before submitting a PR, ensure:

- [ ] All existing features still work
- [ ] New features are tested manually
- [ ] No console errors in browser
- [ ] No errors in backend logs
- [ ] Docker containers start successfully
- [ ] Migrations run without errors

## Pull Request Process

1. Update README.md if needed
2. Update documentation for new features
3. Ensure your code follows the style guide
4. Test your changes thoroughly
5. Write a clear PR description explaining:
   - What changes were made
   - Why they were made
   - How to test them

## Bug Reports

When reporting bugs, please include:

- Description of the bug
- Steps to reproduce
- Expected behavior
- Actual behavior
- Screenshots if applicable
- Environment details (OS, Docker version, etc.)

## Feature Requests

When suggesting features:

- Clearly describe the feature
- Explain the use case
- Provide examples if possible
- Consider blockchain principles and security

## Questions?

Feel free to open an issue for any questions about contributing!

## License

By contributing, you agree that your contributions will be licensed under the same license as this project.
