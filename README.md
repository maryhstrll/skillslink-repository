# GitHub Repositories Guide

## Creating GitHub Repositories

### Method 1: Create Repository on GitHub Website
1. Go to [GitHub.com](https://github.com)
2. Sign in to your account
3. Click the **"+"** icon in the top right corner
4. Select **"New repository"**
5. Fill in repository details:
   - Repository name
   - Description (optional)
   - Choose public or private
   - Initialize with README (optional)
   - Add .gitignore (optional)
   - Choose a license (optional)
6. Click **"Create repository"**

### Method 2: Create Repository via Command Line
```bash
# Create a new directory for your project
mkdir my-new-repository
cd my-new-repository

# Initialize git repository
git init

# Create initial files
echo "# My New Repository" >> README.md
git add README.md
git commit -m "Initial commit"

# Add remote origin (replace with your repository URL)
git remote add origin https://github.com/username/repository-name.git
git branch -M main
git push -u origin main
```

## Cloning Repositories

### Clone via HTTPS
```bash
git clone https://github.com/username/repository-name.git
```

### Clone via SSH
```bash
git clone git@github.com:username/repository-name.git
```

### Clone to Specific Directory
```bash
git clone https://github.com/username/repository-name.git my-local-folder
```

### Clone Specific Branch
```bash
git clone -b branch-name https://github.com/username/repository-name.git
```

### Clone with Depth (Shallow Clone)
```bash
# Clone only the latest commit
git clone --depth 1 https://github.com/username/repository-name.git
```

## Common Post-Clone Actions

### Navigate to Repository
```bash
cd repository-name
```

### Check Repository Status
```bash
git status
git branch -a
git remote -v
```

### Set Up Development Environment
```bash
# Install dependencies (if applicable)
npm install        # For Node.js projects
pip install -r requirements.txt  # For Python projects
composer install   # For PHP projects
```

## Useful Git Commands After Cloning

### Pull Latest Changes
```bash
git pull origin main
```

### Create New Branch
```bash
git checkout -b new-feature-branch
```

### Push Changes
```bash
git add .
git commit -m "Your commit message"
git push origin branch-name
```

### Fork and Clone Workflow
1. Fork the repository on GitHub
2. Clone your fork locally:
   ```bash
   git clone https://github.com/yourusername/forked-repository.git
   ```
3. Add upstream remote:
   ```bash
   git remote add upstream https://github.com/originalowner/original-repository.git
   ```
4. Keep your fork updated:
   ```bash
   git fetch upstream
   git checkout main
   git merge upstream/main
   ```