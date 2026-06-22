<?php

namespace Database\Seeders;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Database\Seeder;

class KbSeeder extends Seeder
{
    public function run(): void
    {
        $rocket = 'M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z';
        $invoice = 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z';
        $doc = 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2';
        $cog = 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z';

        $categories = [
            ['name' => 'Getting Started', 'slug' => 'getting-started', 'icon' => $rocket, 'color' => '#10b981', 'sort_order' => 1, 'description' => 'New to the portal? Start here.'],
            ['name' => 'Invoices & Payments', 'slug' => 'invoices-payments', 'icon' => $invoice, 'color' => '#6d5cff', 'sort_order' => 2, 'description' => 'Understanding your invoices and how to pay.'],
            ['name' => 'Estimates', 'slug' => 'estimates', 'icon' => $doc, 'color' => '#00d4ff', 'sort_order' => 3, 'description' => 'How to review and respond to estimates.'],
            ['name' => 'Account & Settings', 'slug' => 'account-settings', 'icon' => $cog, 'color' => '#f59e0b', 'sort_order' => 4, 'description' => 'Manage your account and preferences.'],
        ];

        $catIds = [];
        foreach ($categories as $cat) {
            $model = KbCategory::updateOrCreate(['slug' => $cat['slug']], $cat + ['is_active' => true]);
            $catIds[$cat['slug']] = $model->id;
        }

        $articles = [
            ['getting-started', 'How to log in to your client portal', 'public',
                "Visit the portal login page and enter the email address we have on file along with your password.\n\nIf you have not set a password yet, use the **Forgot password** link to receive a reset email.\n\n## Tips\n\n- Bookmark the login page for quick access.\n- Passwords are case-sensitive.\n- Contact us if your account is locked."],
            ['getting-started', 'Overview of your dashboard', 'portal_only',
                "Your dashboard gives you a quick snapshot of your account:\n\n- **Outstanding balance** across open invoices\n- **Recent activity** on your account\n- Quick links to invoices, estimates and support\n\nUse the top navigation to move between sections at any time."],
            ['invoices-payments', 'How to download an invoice PDF', 'public',
                "Open any invoice from the **Invoices** section, then click **Download PDF**.\n\nThe PDF includes our bank details and full line-item breakdown so you can process payment through your accounts team."],
            ['invoices-payments', 'How to record or confirm a payment', 'portal_only',
                "Once you have made a bank transfer, reply to your invoice email or open a support ticket with the payment reference.\n\nWe will reconcile the payment and the invoice status will update to **Paid** automatically."],
            ['invoices-payments', 'What do invoice statuses mean?', 'public',
                "- **Draft** — not yet sent to you.\n- **Sent** — awaiting payment.\n- **Overdue** — past the due date.\n- **Paid** — fully settled. Thank you!\n- **Cancelled** — no longer payable."],
            ['estimates', 'How to accept or reject an estimate', 'portal_only',
                "Open the estimate from the **Estimates** section. At the bottom you will find **Accept** and **Reject** buttons.\n\nYou can add an optional note when responding so we know your thoughts."],
            ['estimates', 'What happens after I accept an estimate?', 'portal_only',
                "When you accept an estimate we are notified immediately and will begin work or convert it to an invoice.\n\nYou will receive the corresponding invoice in your portal shortly after."],
            ['account-settings', 'How to change your password', 'portal_only',
                "Go to **My Profile** from the account menu, then use the **Update Password** section.\n\nEnter your current password followed by your new password twice to confirm."],
            ['account-settings', 'How to update your profile', 'portal_only',
                "Open **My Profile** to update your contact details.\n\nKeeping your email current ensures you receive invoices and important notifications."],
        ];

        foreach ($articles as [$catSlug, $title, $visibility, $content]) {
            KbArticle::firstOrCreate(
                ['slug' => KbArticle::uniqueSlug($title)],
                [
                    'category_id' => $catIds[$catSlug],
                    'title' => $title,
                    'content' => $content,
                    'excerpt' => \Illuminate\Support\Str::limit(strip_tags($content), 120),
                    'status' => 'published',
                    'visibility' => $visibility,
                    'published_at' => now(),
                    'author_name' => 'Lumisk Team',
                ]
            );
        }
    }
}
