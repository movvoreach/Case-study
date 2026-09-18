<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiscussionController extends Controller
{
    public function index(Request $request): View
    {
        $courses = Course::orderBy('course_name')->get();

        $discussions = collect([
            [
                'id' => 1,
                'title' => 'តើយើងគួររៀបចំ Auto Layout ក្នុង Figma យ៉ាងដូចម្តេចដើម្បីឱ្យ Responsive បានល្អ?',
                'course_name' => 'Figma UI/UX Masterclass',
                'author_name' => 'សុវណ្ណ សុខ',
                'author_avatar' => 'user.png',
                'replies_count' => 12,
                'views_count' => 145,
                'last_activity' => '10 នាទីមុន',
                'is_pinned' => true,
            ],
            [
                'id' => 2,
                'title' => 'សំណួរអំពីការប្រើប្រាស់ Eloquent Relationship ក្នុង Laravel 11',
                'course_name' => 'Laravel Backend Development',
                'author_name' => 'ពិសិដ្ឋ ហេង',
                'author_avatar' => 'user.png',
                'replies_count' => 8,
                'views_count' => 92,
                'last_activity' => '45 នាទីមុន',
                'is_pinned' => false,
            ],
            [
                'id' => 3,
                'title' => 'របៀបដំឡើង និងកំណត់ TailwindCSS v4 ជាមួយ Vite ក្នុង Project ថ្មី',
                'course_name' => 'Modern Frontend Development',
                'author_name' => 'វណ្ណៈ ចាន់',
                'author_avatar' => 'user.png',
                'replies_count' => 15,
                'views_count' => 210,
                'last_activity' => '2 ម៉ោងមុន',
                'is_pinned' => false,
            ],
            [
                'id' => 4,
                'title' => 'ការអនុវត្តសន្តិសុខបណ្តាញ (Cybersecurity Best Practices) សម្រាប់ API Backend',
                'course_name' => 'Cybersecurity Essentials',
                'author_name' => 'ចាន់ សុភ័ក្ត្រ',
                'author_avatar' => 'user.png',
                'replies_count' => 5,
                'views_count' => 64,
                'last_activity' => '1 ថ្ងៃមុន',
                'is_pinned' => false,
            ],
        ]);

        return view('discussions.index', compact('courses', 'discussions'));
    }
}
