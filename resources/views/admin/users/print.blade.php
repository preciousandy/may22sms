<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Users Export</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial; color: #111827; }
        .muted { color: #6b7280; font-size: 12px; }
        h1 { margin: 0; font-size: 20px; }
        .header { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; vertical-align: top; font-size: 12px; }
        th { background: #f9fafb; text-align: left; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; border: 1px solid #e5e7eb; font-weight: 700; font-size: 11px; }
        .right { text-align: right; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>Users Export</h1>
            <div class="muted">Generated: {{ now()->format('M j, Y H:i') }}</div>
            <div class="muted">Filters: q={{ $q ?? '' }}, status={{ $status ?? 'all' }}</div>
        </div>
        <div class="no-print">
            <button onclick="window.print()" style="padding:8px 12px;border-radius:10px;border:1px solid #111827;background:#111827;color:#fff;font-weight:700;">Print / Save as PDF</button>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Roles</th>
                <th>Status</th>
                <th>Wallet</th>
                <th>Joined</th>
                <th>Last Login</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $u)
                @php
                    $st = (string) ($u->account_status ?? '');
                    $roles = [];
                    if ($u->is_admin) {
                        $roles[] = 'Admin';
                    }
                    if ($u->is_support) {
                        $roles[] = 'Support';
                    }
                    if ($u->is_editor) {
                        $roles[] = 'Editor';
                    }
                    if (empty($roles)) {
                        $roles[] = 'User';
                    }
                @endphp
                <tr>
                    <td>#{{ $u->id }}</td>
                    <td>
                        <div style="font-weight:800;">{{ $u->name }}</div>
                        <div class="muted">{{ $u->email }}</div>
                        <div class="muted">{{ $u->phone_number }}</div>
                    </td>
                    <td>{{ implode(', ', $roles) }}</td>
                    <td><span class="badge">{{ strtoupper($st ?: 'UNKNOWN') }}</span></td>
                    <td class="right">${{ number_format((float) ($u->walletBalance ?? 0), 2) }}</td>
                    <td>{{ $u->created_at ? \Illuminate\Support\Carbon::parse($u->created_at)->format('M j, Y') : '—' }}</td>
                    <td>{{ $u->last_login_at ? \Illuminate\Support\Carbon::parse($u->last_login_at)->format('M j, Y H:i') : '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
