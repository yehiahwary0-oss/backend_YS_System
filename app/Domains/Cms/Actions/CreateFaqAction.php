<?php

namespace App\Domains\Cms\Actions;

use App\Domains\Cms\Models\Faq;
use Illuminate\Support\Facades\Auth;

class CreateFaqAction
{
    public function execute(array $data): Faq
    {
        return Faq::create([
            'question_en' => $data['question_en'],
            'question_ar' => $data['question_ar'],
            'answer_en'   => $data['answer_en'],
            'answer_ar'   => $data['answer_ar'],
            'category'    => $data['category'] ?? null,
            'status'      => $data['status'] ?? 'draft',
            'sort_order'  => $data['sort_order'] ?? 0,
            'created_by'  => Auth::id(),
        ]);
    }
}
