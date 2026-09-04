# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.x.x   | :white_check_mark: |

## Reporting a Vulnerability

We take the security of SIAKAD seriously. If you believe you have found a security vulnerability, please report it to us as described below.

### How to Report

**Please do not report security vulnerabilities through public GitHub issues.**

Instead, please report them via email to [anakriryanda@gmail.com](mailto:anakriryanda@gmail.com).

You should receive a response within 48 hours. If for some reason you do not, please follow up via email to ensure we received your original message.

Please include the following information in your report:

- Type of issue (e.g. buffer overflow, SQL injection, cross-site scripting, etc.)
- Full paths of source file(s) related to the manifestation of the issue
- The location of the affected source code (tag/branch/commit or direct URL)
- Any special configuration required to reproduce the issue
- Step-by-step instructions to reproduce the issue
- Proof-of-concept or exploit code (if possible)
- Impact of the issue, including how an attacker might exploit the issue

### What to Expect

- We will acknowledge your email within 48 hours
- We will send a more detailed response within 72 hours indicating the next steps
- We will keep you informed of the progress towards a fix
- We may ask for additional information or guidance

### Security Measures in Place

SIAKAD implements several security measures:

1. **Authentication & Authorization**
   - Laravel Breeze for authentication
   - Role-based access control (superadmin, admin_fakultas, dosen, mahasiswa)
   - Faculty-scoped admin access

2. **Input Validation**
   - Laravel Form Requests for validation
   - Custom exception handling

3. **Rate Limiting**
   - AI Chat: 10 requests/minute
   - KRS Operations: 10 requests/minute
   - Penilaian: 20 requests/minute

4. **Security Headers**
   - Custom SecurityHeadersMiddleware
   - CSRF protection on all forms

5. **Database Security**
   - Parameterized queries (Eloquent ORM)
   - No raw SQL without proper escaping

## Best Practices for Deployment

1. **Environment Variables**
   - Never commit `.env` file
   - Use strong, unique passwords
   - Rotate API keys regularly

2. **HTTPS**
   - Always use HTTPS in production
   - Configure proper SSL certificates

3. **Database**
   - Use strong database passwords
   - Limit database user permissions
   - Regular backups

4. **Updates**
   - Keep Laravel and dependencies updated
   - Monitor security advisories

## Acknowledgments

We appreciate the security research community and will acknowledge researchers who report valid vulnerabilities.
