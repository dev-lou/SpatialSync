---
description: Rules for safe database schema changes. Never delete existing migrations. Provides patterns for Laravel, Django, and raw SQL.
---

# UI/UX Database Migration Rules

> Safe schema change patterns for production databases.
> Never delete migrations. Always create new ones.

---

## GOLDEN RULES

1. **NEVER delete an existing migration file**
2. **NEVER modify a migration that has been run in production**
3. Always create NEW migrations for changes
4. Test migrations on a copy of production data
5. Have a rollback plan for every migration

---

## OVERVIEW

This skill covers safe migration patterns for:
- Adding columns
- Removing columns
- Renaming columns
- Changing column types
- Adding/removing indexes
- Data migrations

---

## LARAVEL MIGRATIONS

### Add a New Column

```php
// database/migrations/2026_01_15_100000_add_phone_to_users_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }
};
```

### Remove a Column (Safe Way)

```php
// database/migrations/2026_01_16_100000_remove_legacy_field_from_users_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('legacy_field');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Restore column with nullable to prevent data loss issues
            $table->string('legacy_field')->nullable()->after('email');
        });
    }
};
```

### Rename a Column

```php
// database/migrations/2026_01_17_100000_rename_name_to_full_name_in_users_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('name', 'full_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('full_name', 'name');
        });
    }
};
```

### Change Column Type

```php
// database/migrations/2026_01_18_100000_change_bio_to_text_in_users_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('bio')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bio', 255)->nullable()->change();
        });
    }
};
```

### Add Index

```php
// database/migrations/2026_01_19_100000_add_email_index_to_users_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('email');
            // Or unique index:
            // $table->unique('email');
            // Or composite index:
            // $table->index(['first_name', 'last_name']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
        });
    }
};
```

### Data Migration (with Safety)

```php
// database/migrations/2026_01_20_100000_migrate_user_names.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Process in batches to avoid memory issues
        DB::table('users')
            ->whereNull('full_name')
            ->orderBy('id')
            ->chunk(1000, function ($users) {
                foreach ($users as $user) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'full_name' => trim($user->first_name . ' ' . $user->last_name)
                        ]);
                }
            });
    }

    public function down(): void
    {
        // Data migrations often can't be reversed
        // Log a warning or skip
    }
};
```

---

## DJANGO MIGRATIONS

### Add a New Field

```python
# Generated by Django, then modify as needed
# app/migrations/0002_add_phone_to_user.py

from django.db import migrations, models

class Migration(migrations.Migration):

    dependencies = [
        ('app', '0001_initial'),
    ]

    operations = [
        migrations.AddField(
            model_name='user',
            name='phone',
            field=models.CharField(max_length=20, blank=True, null=True),
        ),
    ]
```

### Remove a Field

```python
# app/migrations/0003_remove_legacy_field.py

from django.db import migrations

class Migration(migrations.Migration):

    dependencies = [
        ('app', '0002_add_phone_to_user'),
    ]

    operations = [
        migrations.RemoveField(
            model_name='user',
            name='legacy_field',
        ),
    ]
```

### Rename a Field

```python
# app/migrations/0004_rename_name_to_full_name.py

from django.db import migrations

class Migration(migrations.Migration):

    dependencies = [
        ('app', '0003_remove_legacy_field'),
    ]

    operations = [
        migrations.RenameField(
            model_name='user',
            old_name='name',
            new_name='full_name',
        ),
    ]
```

### Data Migration

```python
# app/migrations/0005_migrate_user_names.py

from django.db import migrations

def migrate_names(apps, schema_editor):
    User = apps.get_model('app', 'User')
    
    # Process in batches
    batch_size = 1000
    users = User.objects.filter(full_name__isnull=True)
    
    for i in range(0, users.count(), batch_size):
        batch = users[i:i + batch_size]
        for user in batch:
            user.full_name = f"{user.first_name} {user.last_name}".strip()
        User.objects.bulk_update(batch, ['full_name'])

def reverse_migrate(apps, schema_editor):
    pass  # Data migrations often can't be reversed

class Migration(migrations.Migration):

    dependencies = [
        ('app', '0004_rename_name_to_full_name'),
    ]

    operations = [
        migrations.RunPython(migrate_names, reverse_migrate),
    ]
```

---

## RAW SQL MIGRATIONS

### Add Column

```sql
-- Migration: 001_add_phone_to_users.sql
-- Up
ALTER TABLE users 
ADD COLUMN phone VARCHAR(20) NULL AFTER email;

-- Down
ALTER TABLE users 
DROP COLUMN phone;
```

### Remove Column (Safe Way)

```sql
-- Migration: 002_remove_legacy_field.sql
-- Up
ALTER TABLE users 
DROP COLUMN legacy_field;

-- Down (restore as nullable)
ALTER TABLE users 
ADD COLUMN legacy_field VARCHAR(255) NULL;
```

### Rename Column

```sql
-- Migration: 003_rename_name_column.sql
-- Up (MySQL)
ALTER TABLE users 
CHANGE COLUMN name full_name VARCHAR(255);

-- Up (PostgreSQL)
ALTER TABLE users 
RENAME COLUMN name TO full_name;

-- Down (MySQL)
ALTER TABLE users 
CHANGE COLUMN full_name name VARCHAR(255);

-- Down (PostgreSQL)
ALTER TABLE users 
RENAME COLUMN full_name TO name;
```

### Add Index

```sql
-- Migration: 004_add_email_index.sql
-- Up
CREATE INDEX idx_users_email ON users(email);

-- Down
DROP INDEX idx_users_email ON users;
```

### Change Column Type (PostgreSQL)

```sql
-- Migration: 005_change_bio_to_text.sql
-- Up
ALTER TABLE users 
ALTER COLUMN bio TYPE TEXT;

-- Down
ALTER TABLE users 
ALTER COLUMN bio TYPE VARCHAR(255);
```

### Change Column Type (MySQL)

```sql
-- Migration: 005_change_bio_to_text.sql
-- Up
ALTER TABLE users 
MODIFY COLUMN bio TEXT;

-- Down
ALTER TABLE users 
MODIFY COLUMN bio VARCHAR(255);
```

---

## ZERO-DOWNTIME MIGRATION PATTERNS

### Pattern 1: Add Column (Safe)

```
1. Add column as nullable
2. Deploy code that writes to both old and new
3. Backfill data
4. Deploy code that reads from new
5. (Optional) Make column non-nullable
6. (Optional) Remove old column
```

### Pattern 2: Rename Column (Safe)

```
1. Add new column
2. Deploy code that writes to both
3. Backfill data from old to new
4. Deploy code that reads from new
5. Remove old column
```

### Pattern 3: Remove Column (Safe)

```
1. Deploy code that doesn't read from column
2. Deploy code that doesn't write to column
3. Remove column from database
```

### Laravel Zero-Downtime Example

```php
// Step 1: Add new column
Schema::table('users', function (Blueprint $table) {
    $table->string('email_address')->nullable();
});

// Step 2: Code writes to both (in User model)
protected static function boot()
{
    parent::boot();
    
    static::saving(function ($user) {
        $user->email_address = $user->email;
    });
}

// Step 3: Backfill (run as job)
User::whereNull('email_address')
    ->chunk(1000, function ($users) {
        foreach ($users as $user) {
            $user->update(['email_address' => $user->email]);
        }
    });

// Step 4: Switch reads to new column
// Step 5: Remove old column
Schema::table('users', function (Blueprint $table) {
    $table->dropColumn('email');
});
```

---

## MIGRATION NAMING CONVENTIONS

```
YYYY_MM_DD_HHMMSS_action_target_table.php

Actions:
- create    → create_users_table
- add       → add_phone_to_users_table
- remove    → remove_legacy_from_users_table
- rename    → rename_name_to_full_name_in_users_table
- change    → change_bio_type_in_users_table
- add_index → add_email_index_to_users_table
- drop_index → drop_email_index_from_users_table

Examples:
2026_01_15_100000_create_users_table.php
2026_01_16_100000_add_avatar_to_users_table.php
2026_01_17_100000_remove_legacy_field_from_users_table.php
2026_01_18_100000_rename_name_to_full_name_in_users_table.php
2026_01_19_100000_change_bio_to_text_in_users_table.php
```

---

## CHECKLIST BEFORE RUNNING MIGRATIONS

- [ ] Tested migration on local database
- [ ] Tested migration on staging with production data copy
- [ ] Rollback tested (`migrate:rollback`)
- [ ] Backup created before running
- [ ] Team notified of migration
- [ ] Off-peak hours chosen for large migrations
- [ ] Monitoring in place during migration
- [ ] Rollback plan documented

---

## COMMON MISTAKES TO AVOID

| ❌ Wrong | ✅ Right |
|----------|----------|
| Delete old migration file | Create new migration to reverse changes |
| Edit migration after it's run | Create new migration with changes |
| Add non-nullable column without default | Add as nullable, then make non-nullable |
| Rename column directly | Add new, migrate data, remove old |
| Large data migration in single transaction | Process in batches |
| Run migration during peak hours | Schedule for off-peak |

---

## EMERGENCY ROLLBACK

### Laravel

```bash
# Rollback last migration
php artisan migrate:rollback

# Rollback specific steps
php artisan migrate:rollback --step=3

# Rollback all
php artisan migrate:reset

# Fresh (drop all, run all) - NEVER IN PRODUCTION
php artisan migrate:fresh
```

### Django

```bash
# Rollback to specific migration
python manage.py migrate app 0004

# Show migration history
python manage.py showmigrations
```

### Raw SQL

```bash
# Have rollback script ready
mysql -u user -p database < rollback_005.sql
```
