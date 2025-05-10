<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterviewQuestion extends Model
{
    protected $table = 'interview_questions'; // Если имя модели не совпадает с таблицей
    protected $fillable = ['question', 'answer_short', 'answer_full']; // Разрешаем массовое присвоение

}
