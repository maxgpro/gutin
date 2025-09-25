#!/bin/bash

# Скрипт для управления HH файлами в Git

HH_FILES=(
    "app/Models/HhAccount.php"
    "app/Services/HhApi.php"
    "app/Http/Controllers/HhAuthController.php"
    "app/Http/Middleware/HhAccessMiddleware.php"
    "app/Http/Middleware/EnsureHhToken.php"
    "app/Console/Commands/CheckHhAuth.php"
    "routes/hh.php"
    "database/migrations/2025_09_14_131659_create_hh_accounts_table.php"
    "tests/Feature/HhAccessMiddlewareTest.php"
    "tests/Feature/HhButtonVisibilityTest.php"
)

case "$1" in
    "skip")
        echo "Пропускаем HH файлы в рабочем дереве..."
        for file in "${HH_FILES[@]}"; do
            if [ -f "$file" ]; then
                git update-index --skip-worktree "$file"
                echo "✓ $file"
            fi
        done
        echo "HH файлы теперь не будут коммититься, но останутся в истории."
        ;;
    "unskip")
        echo "Возвращаем отслеживание HH файлов..."
        for file in "${HH_FILES[@]}"; do
            if [ -f "$file" ]; then
                git update-index --no-skip-worktree "$file"
                echo "✓ $file"
            fi
        done
        echo "HH файлы снова отслеживаются Git'ом."
        ;;
    "status")
        echo "Статус HH файлов:"
        for file in "${HH_FILES[@]}"; do
            if [ -f "$file" ]; then
                if git ls-files -v "$file" | grep -q '^S'; then
                    echo "🔒 $file (skip-worktree)"
                else
                    echo "👁  $file (отслеживается)"
                fi
            else
                echo "❌ $file (не найден)"
            fi
        done
        ;;
    "remove")
        echo "ВНИМАНИЕ: Это удалит HH файлы из репозитория навсегда!"
        read -p "Продолжить? (y/N): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            for file in "${HH_FILES[@]}"; do
                if [ -f "$file" ]; then
                    git rm --cached "$file"
                    echo "🗑️  $file"
                fi
            done
            echo "HH файлы удалены из индекса. Сделайте коммит для применения изменений."
        else
            echo "Отменено."
        fi
        ;;
    *)
        echo "Использование: $0 {skip|unskip|status|remove}"
        echo ""
        echo "Команды:"
        echo "  skip    - Пропустить HH файлы (не коммитить изменения)"
        echo "  unskip  - Вернуть отслеживание HH файлов"
        echo "  status  - Показать статус HH файлов"
        echo "  remove  - Удалить HH файлы из репозитория (ОПАСНО!)"
        echo ""
        echo "Файлы будут продолжать существовать локально, но не будут"
        echo "отправляться в удаленный репозиторий."
        exit 1
        ;;
esac