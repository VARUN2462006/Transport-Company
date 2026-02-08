---
description: How to use Git in this Transport Company project
---

# Git Workflow for Transport Company Project

## Project Setup
- **Repository**: https://github.com/VARUN2462006/Transport-Company.git
- **Main Branch**: `main`
- **Remote**: `origin`

---

## Basic Git Workflow

### 1. Check Current Status
Before making any changes, always check what's changed:
```bash
git status
```

### 2. View Changes Made
To see what you've modified:
```bash
git diff
```

To see changes in a specific file:
```bash
git diff path/to/file.php
```

### 3. Stage Your Changes
Add specific files:
```bash
git add filename.php
```

Add all changed files:
```bash
git add .
```

Add all PHP files:
```bash
git add *.php
```

### 4. Commit Your Changes
Commit with a descriptive message:
```bash
git commit -m "Add GPS tracking feature for trucks"
```

Best practices for commit messages:
- Use present tense ("Add feature" not "Added feature")
- Be specific about what changed
- Examples:
  - `git commit -m "Add truck location tracking page"`
  - `git commit -m "Fix admin login authentication bug"`
  - `git commit -m "Update database schema for GPS coordinates"`

### 5. Push to GitHub
Push your commits to the remote repository:
```bash
git push origin main
```

If this is your first push:
```bash
git push -u origin main
```

### 6. Pull Latest Changes
Before starting work, always pull the latest changes:
```bash
git pull origin main
```

---

## Common Scenarios

### Scenario 1: Making Changes to Existing Files
```bash
# 1. Pull latest changes
git pull origin main

# 2. Make your changes to files (e.g., edit track_truck.php)

# 3. Check what changed
git status
git diff

# 4. Stage and commit
git add track_truck.php
git commit -m "Add auto-refresh feature to truck tracking"

# 5. Push to GitHub
git push origin main
```

### Scenario 2: Adding New Features
```bash
# 1. Pull latest
git pull origin main

# 2. Create your new files (e.g., add_truck.php, update_location.php)

# 3. Stage all new files
git add add_truck.php update_location.php manage_trucks.php
# Or add everything: git add .

# 4. Commit
git commit -m "Implement truck management system for admin"

# 5. Push
git push origin main
```

### Scenario 3: Database Changes
When you modify database structure:
```bash
# 1. Export your SQL changes
# (manually or use mysqldump)

# 2. Add SQL file
git add database/updates.sql

# 3. Commit with clear description
git commit -m "Add trucks and truck_locations tables to database"

# 4. Push
git push origin main
```

### Scenario 4: Undo Uncommitted Changes
If you want to discard changes to a file:
```bash
git checkout -- filename.php
```

Discard all uncommitted changes:
```bash
git reset --hard
```

⚠️ **Warning**: This permanently deletes your uncommitted changes!

### Scenario 5: View Commit History
See recent commits:
```bash
git log --oneline -10
```

See detailed history:
```bash
git log
```

See changes in a specific commit:
```bash
git show <commit-hash>
```

---

## Recommended .gitignore File

Create a `.gitignore` file to exclude sensitive and unnecessary files:

```
# Database configuration (contains passwords)
config.php
db_connection.php

# XAMPP/Apache logs
*.log

# PHP temporary files
*.tmp
*.temp

# IDE/Editor files
.vscode/
.idea/
*.swp
*.swo
*~

# OS files
.DS_Store
Thumbs.db
desktop.ini

# Backup files
*.bak
*.backup
*~

# Uploaded files (if you don't want to track them)
uploads/*
!uploads/.gitkeep

# Vendor dependencies (if using Composer)
vendor/
```

---

## Branch Strategy (Optional - For Advanced Use)

### Creating a Feature Branch
When working on a major feature:
```bash
# Create and switch to new branch
git checkout -b feature/gps-tracking

# Make your changes...

# Commit changes
git add .
git commit -m "Implement GPS tracking feature"

# Push branch to GitHub
git push origin feature/gps-tracking

# Switch back to main
git checkout main

# Merge feature into main
git merge feature/gps-tracking

# Push updated main
git push origin main

# Delete feature branch (optional)
git branch -d feature/gps-tracking
```

---

## Quick Reference Commands

| Command | Description |
|---------|-------------|
| `git status` | Check current state |
| `git add <file>` | Stage specific file |
| `git add .` | Stage all changes |
| `git commit -m "message"` | Commit staged changes |
| `git push origin main` | Push to GitHub |
| `git pull origin main` | Pull latest changes |
| `git log --oneline` | View commit history |
| `git diff` | View unstaged changes |
| `git checkout -- <file>` | Discard changes to file |
| `git reset --hard` | Discard all uncommitted changes |
| `git branch` | List branches |
| `git checkout -b <branch>` | Create new branch |

---

## Best Practices

1. **Commit Often**: Make small, focused commits rather than large ones
2. **Pull Before Push**: Always `git pull` before `git push` to avoid conflicts
3. **Write Clear Messages**: Commit messages should explain what and why
4. **Don't Commit Sensitive Data**: Never commit passwords, API keys, or database credentials
5. **Use .gitignore**: Exclude unnecessary files from version control
6. **Review Before Committing**: Use `git diff` to review your changes
7. **Backup Important Data**: Git is not a backup solution for database data

---

## Troubleshooting

### Merge Conflicts
If you get a merge conflict:
```bash
# 1. Pull changes
git pull origin main
# (You'll see conflict messages)

# 2. Open conflicted files and resolve conflicts manually
# Look for markers: <<<<<<< HEAD, =======, >>>>>>>

# 3. After fixing, stage the resolved files
git add conflicted-file.php

# 4. Complete the merge
git commit -m "Resolve merge conflict in conflicted-file.php"

# 5. Push
git push origin main
```

### Accidentally Committed Wrong Files
```bash
# Undo last commit but keep changes
git reset --soft HEAD~1

# Unstage files if needed
git reset HEAD filename.php

# Re-commit correctly
git add correct-files.php
git commit -m "Correct commit message"
```

---

## Daily Workflow Summary

**Morning (Start of Work):**
```bash
git pull origin main
```

**During Work (After completing a feature/fix):**
```bash
git status
git add .
git commit -m "Descriptive message"
git push origin main
```

**End of Day:**
```bash
git status  # Make sure everything is committed
git push origin main  # Push any remaining work
```

---

## Need Help?
- Check Git status: `git status`
- View Git help: `git --help`
- View command-specific help: `git <command> --help` (e.g., `git commit --help`)
