<?php
declare(strict_types=1);

class PurposeController
{
    public function index(): void
    {
        $userId = Auth::id();
        $plan = Database::row('SELECT * FROM safety_plans WHERE user_id = ?', [$userId]);
        $goals = Database::rows(
            'SELECT * FROM goals WHERE user_id = ? ORDER BY completed_at IS NULL DESC, created_at DESC LIMIT 12',
            [$userId]
        );
        $testimony = Database::row('SELECT * FROM testimonies WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1', [$userId]);

        view('purpose/index', [
            'title' => 'Purpose',
            'plan' => $plan,
            'goals' => $goals,
            'testimony' => $testimony,
        ]);
    }

    public function saveSafetyPlan(): void
    {
        $userId = Auth::id();
        $data = [
            'top_trigger' => sanitize((string) ($_POST['top_trigger'] ?? '')),
            'escape_action' => sanitize((string) ($_POST['escape_action'] ?? '')),
            'support_contact' => sanitize((string) ($_POST['support_contact'] ?? '')),
            'truth_statement' => sanitize((string) ($_POST['truth_statement'] ?? '')),
            'updated_at' => now(),
        ];

        $existing = Database::row('SELECT id FROM safety_plans WHERE user_id = ?', [$userId]);
        if ($existing) {
            Database::query(
                'UPDATE safety_plans SET top_trigger = ?, escape_action = ?, support_contact = ?, truth_statement = ?, updated_at = ? WHERE user_id = ?',
                [$data['top_trigger'], $data['escape_action'], $data['support_contact'], $data['truth_statement'], $data['updated_at'], $userId]
            );
        } else {
            $data['user_id'] = $userId;
            $data['created_at'] = now();
            Database::insert('safety_plans', $data);
        }

        flash('success', 'Safety plan saved.');
        redirect('/purpose');
    }

    public function storeGoal(): void
    {
        $title = sanitize((string) ($_POST['title'] ?? ''));
        if ($title === '') {
            flash('error', 'Add a goal title.');
            redirect('/purpose');
        }

        Database::insert('goals', [
            'user_id' => Auth::id(),
            'category' => sanitize((string) ($_POST['category'] ?? 'spiritual')),
            'title' => $title,
            'description' => sanitize((string) ($_POST['description'] ?? '')),
            'target_date' => ($_POST['target_date'] ?? '') !== '' ? $_POST['target_date'] : null,
            'created_at' => now(),
        ]);

        flash('success', 'Purpose goal added.');
        redirect('/purpose');
    }

    public function completeGoal(string $goalId): void
    {
        Database::query(
            'UPDATE goals SET completed_at = ? WHERE id = ? AND user_id = ? AND completed_at IS NULL',
            [now(), (int) $goalId, Auth::id()]
        );

        flash('success', 'Goal marked complete.');
        redirect('/purpose');
    }

    public function saveTestimony(): void
    {
        $userId = Auth::id();
        $title = sanitize((string) ($_POST['title'] ?? 'My testimony'));
        $body = sanitize((string) ($_POST['body'] ?? ''));
        $isPublic = isset($_POST['is_public']) ? 1 : 0;

        if ($body === '') {
            flash('error', 'Add a few lines to your testimony draft.');
            redirect('/purpose');
        }

        $existing = Database::row('SELECT id FROM testimonies WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1', [$userId]);
        if ($existing) {
            Database::query(
                'UPDATE testimonies SET title = ?, body = ?, is_public = ?, updated_at = ? WHERE id = ? AND user_id = ?',
                [$title, $body, $isPublic, now(), $existing['id'], $userId]
            );
        } else {
            Database::insert('testimonies', [
                'user_id' => $userId,
                'title' => $title,
                'body' => $body,
                'is_public' => $isPublic,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        flash('success', 'Testimony draft saved.');
        redirect('/purpose');
    }
}
