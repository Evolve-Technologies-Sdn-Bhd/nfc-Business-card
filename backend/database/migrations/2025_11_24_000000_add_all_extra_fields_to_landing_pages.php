<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for all landing_pages extra fields
 * Merged from 8 separate migrations:
 * - add_design_config_to_landing_pages
 * - add_repeater_fields_to_landing_pages_table
 * - add_cover_banner_path_to_landing_pages
 * - add_features_to_landing_pages
 * - add_color_scheme_layout_to_landing_pages
 * - add_portfolio_blog_to_landing_pages
 * - add_blog_extra_fields_to_landing_pages
 * - add_portfolio_extra_fields_to_landing_pages
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            // ============ PROFILE EXTRA FIELDS ============
            if (!Schema::hasColumn('landing_pages', 'pronouns')) {
                $table->string('pronouns')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'tagline')) {
                $table->string('tagline')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'cover_banner')) {
                $table->text('cover_banner')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'cover_banner_path')) {
                $table->string('cover_banner_path')->nullable();
            }
            
            // ============ REPEATER/JSON FIELDS ============
            if (!Schema::hasColumn('landing_pages', 'education')) {
                $table->json('education')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'certifications')) {
                $table->json('certifications')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'expertise')) {
                $table->json('expertise')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'awards')) {
                $table->json('awards')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'working_hours')) {
                $table->json('working_hours')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'service_features')) {
                $table->json('service_features')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'service_tags')) {
                $table->json('service_tags')->nullable();
            }
            
            // ============ DESIGN CONFIG ============
            if (!Schema::hasColumn('landing_pages', 'design_config')) {
                $table->json('design_config')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'visible_fields')) {
                $table->json('visible_fields')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'features')) {
                $table->json('features')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'feature_order')) {
                $table->json('feature_order')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'color_scheme')) {
                $table->string('color_scheme', 50)->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'layout')) {
                $table->string('layout', 50)->nullable();
            }
            
            // ============ COMPANY EXTRA FIELDS ============
            if (!Schema::hasColumn('landing_pages', 'company_description')) {
                $table->text('company_description')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'company_video')) {
                $table->text('company_video')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'industry')) {
                $table->string('industry', 100)->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'established_year')) {
                $table->string('established_year', 10)->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'employee_count')) {
                $table->string('employee_count', 50)->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'postal_code')) {
                $table->string('postal_code', 20)->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'coordinates')) {
                $table->string('coordinates', 100)->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'company_whatsapp')) {
                $table->string('company_whatsapp', 50)->nullable();
            }
            
            // ============ SERVICES EXTRA FIELDS ============
            if (!Schema::hasColumn('landing_pages', 'service_name')) {
                $table->text('service_name')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'service_category')) {
                $table->string('service_category', 100)->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'service_image')) {
                $table->text('service_image')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'service_video')) {
                $table->text('service_video')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'service_description')) {
                $table->text('service_description')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'service_price')) {
                $table->string('service_price', 50)->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'service_old_price')) {
                $table->string('service_old_price', 50)->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'service_duration')) {
                $table->string('service_duration', 100)->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'service_brochure')) {
                $table->text('service_brochure')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'booking_enabled')) {
                $table->boolean('booking_enabled')->default(false);
            }
            if (!Schema::hasColumn('landing_pages', 'booking_url')) {
                $table->text('booking_url')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'gallery')) {
                $table->json('gallery')->nullable();
            }
            
            // ============ PORTFOLIO FIELDS ============
            if (!Schema::hasColumn('landing_pages', 'portfolio_title')) {
                $table->text('portfolio_title')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'portfolio_description')) {
                $table->text('portfolio_description')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'portfolio_category')) {
                $table->text('portfolio_category')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'portfolio_tags')) {
                $table->json('portfolio_tags')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'portfolio_cover_image')) {
                $table->text('portfolio_cover_image')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'portfolio_gallery')) {
                $table->json('portfolio_gallery')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'projects')) {
                $table->json('projects')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'project_url')) {
                $table->text('project_url')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'date_completed')) {
                $table->text('date_completed')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'client_name')) {
                $table->text('client_name')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'portfolio_location')) {
                $table->text('portfolio_location')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'skills_used')) {
                $table->json('skills_used')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'pdf_download')) {
                $table->text('pdf_download')->nullable();
            }
            
            // ============ BLOG FIELDS ============
            if (!Schema::hasColumn('landing_pages', 'blog_enabled')) {
                $table->boolean('blog_enabled')->default(false);
            }
            if (!Schema::hasColumn('landing_pages', 'blog_posts')) {
                $table->json('blog_posts')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'blog_gallery')) {
                $table->json('blog_gallery')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'blog_title')) {
                $table->text('blog_title')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'blog_slug')) {
                $table->text('blog_slug')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'blog_cover_image')) {
                $table->text('blog_cover_image')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'blog_category')) {
                $table->string('blog_category', 100)->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'blog_tags')) {
                $table->json('blog_tags')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'author_name')) {
                $table->text('author_name')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'published_date')) {
                $table->string('published_date', 50)->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'reading_time')) {
                $table->string('reading_time', 50)->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'blog_content')) {
                $table->text('blog_content')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'external_link')) {
                $table->text('external_link')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'related_posts')) {
                $table->json('related_posts')->nullable();
            }
            
            // ============ LINKS EXTRA FIELDS ============
            if (!Schema::hasColumn('landing_pages', 'appointment_link')) {
                $table->text('appointment_link')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'payment_button_text')) {
                $table->string('payment_button_text', 100)->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'payment_button_url')) {
                $table->text('payment_button_url')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            $columns = [
                // Profile
                'pronouns', 'tagline', 'cover_banner', 'cover_banner_path',
                // Repeater/JSON
                'education', 'certifications', 'expertise', 'awards', 'working_hours',
                'service_features', 'service_tags',
                // Design
                'design_config', 'visible_fields', 'features', 'feature_order',
                'color_scheme', 'layout',
                // Company
                'company_description', 'company_video', 'industry', 'established_year',
                'employee_count', 'postal_code', 'coordinates', 'company_whatsapp',
                // Services
                'service_name', 'service_category', 'service_image', 'service_video',
                'service_description', 'service_price', 'service_old_price', 'service_duration',
                'service_brochure', 'booking_enabled', 'booking_url', 'gallery',
                // Portfolio
                'portfolio_title', 'portfolio_description', 'portfolio_category', 'portfolio_tags',
                'portfolio_cover_image', 'portfolio_gallery', 'projects', 'project_url',
                'date_completed', 'client_name', 'portfolio_location', 'skills_used', 'pdf_download',
                // Blog
                'blog_enabled', 'blog_posts', 'blog_gallery', 'blog_title', 'blog_slug',
                'blog_cover_image', 'blog_category', 'blog_tags', 'author_name',
                'published_date', 'reading_time', 'blog_content', 'external_link', 'related_posts',
                // Links
                'appointment_link', 'payment_button_text', 'payment_button_url',
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('landing_pages', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
