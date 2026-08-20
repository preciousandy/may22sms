<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicketMessage extends Model
{
    protected $fillable = [
        'support_ticket_id',
        'user_id',
        'is_admin',
        'message',
        'attachment_path',
        'attachment_name',
        'attachment_type',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
    ];

    public function isImage(): bool
    {
        if ($this->attachment_type === 'image') {
            return true;
        }

        if (empty($this->attachment_name)) {
            return false;
        }

        $ext = strtolower(pathinfo($this->attachment_name, PATHINFO_EXTENSION));
        return in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'], true);
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if (empty($this->attachment_path)) {
            return null;
        }

        return asset('storage/' . $this->attachment_path);
    }

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
