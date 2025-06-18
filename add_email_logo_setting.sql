-- Check if email_logo_url setting already exists
INSERT INTO settings (
    `key`, 
    `value`, 
    `group`, 
    `type`, 
    `label`, 
    `description`, 
    `order`, 
    `created_at`, 
    `updated_at`
)
SELECT 
    'email_logo_url', 
    '', 
    'emails', 
    'text', 
    'Email Logo URL', 
    'URL for the logo displayed in email templates', 
    14, 
    NOW(), 
    NOW()
FROM dual
WHERE NOT EXISTS (
    SELECT 1 FROM settings WHERE `key` = 'email_logo_url'
);

-- If the setting exists but is in a different group, update it
UPDATE settings 
SET 
    `group` = 'emails', 
    `type` = 'text', 
    `label` = 'Email Logo URL', 
    `description` = 'URL for the logo displayed in email templates', 
    `updated_at` = NOW()
WHERE `key` = 'email_logo_url'; 