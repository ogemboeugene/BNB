<?php

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="BNB Management System API",
 *     description="A comprehensive Laravel API for managing Bed & Breakfast properties with advanced features including geolocation search, reviews, support tickets, and analytics.",
 *     @OA\Contact(
 *         email="api@bnbmanagement.com",
 *         name="BNB Management API Support"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000/api/v1",
 *     description="Development Server"
 * )
 *
 * @OA\Server(
 *     url="https://bnb-backend-fpe8ejhwhah5eubp.uaenorth-01.azurewebsites.net/api/v1",
 *     description="Azure Production Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Laravel Sanctum token authentication. Get your token from /auth/login endpoint, then enter 'Bearer [your-token]' in the Authorization header."
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication and authorization endpoints"
 * )
 *
 * @OA\Tag(
 *     name="BNBs",
 *     description="BNB property management endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Search",
 *     description="Advanced search and filtering endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Reviews",
 *     description="Review and rating system endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Support",
 *     description="Customer support ticket system endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Availability",
 *     description="Calendar and availability management endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Analytics",
 *     description="Analytics and dashboard endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Notifications",
 *     description="Notification management endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Health",
 *     description="API health and monitoring endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Admin",
 *     description="Administrative endpoints (Admin role required)"
 * )
 *
 * @OA\Schema(
 *     schema="BNB",
 *     type="object",
 *     title="BNB Property",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Downtown Apartment"),
 *     @OA\Property(property="location", type="string", example="New York, NY"),
 *     @OA\Property(property="description", type="string", example="A beautiful apartment in downtown"),
 *     @OA\Property(property="latitude", type="number", format="float", example=40.7128),
 *     @OA\Property(property="longitude", type="number", format="float", example=-74.0060),
 *     @OA\Property(property="price_per_night", type="string", example="150.00"),
 *     @OA\Property(property="max_guests", type="integer", example=4),
 *     @OA\Property(property="bedrooms", type="integer", example=2),
 *     @OA\Property(property="bathrooms", type="integer", example=1),
 *     @OA\Property(property="amenities", type="array", @OA\Items(type="string"), example={"wifi", "pool", "parking"}),
 *     @OA\Property(property="availability", type="boolean", example=true),
 *     @OA\Property(property="average_rating", type="number", format="float", example=4.2),
 *     @OA\Property(property="total_reviews", type="integer", example=15),
 *     @OA\Property(property="image_url", type="string", example="https://res.cloudinary.com/demo/image/upload/sample.jpg"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-08T10:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-08T10:00:00.000000Z")
 * )
 *
 * @OA\Schema(
 *     schema="Review",
 *     type="object",
 *     title="Review",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="bnb_id", type="integer", example=1),
 *     @OA\Property(property="rating", type="integer", example=5),
 *     @OA\Property(property="comment", type="string", example="Amazing property! Clean and comfortable."),
 *     @OA\Property(property="feedback_categories", type="array", @OA\Items(type="string"), example={"cleanliness", "communication"}),
 *     @OA\Property(property="is_verified", type="boolean", example=false),
 *     @OA\Property(property="stay_date", type="string", format="date-time", example="2025-08-15T00:00:00.000000Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-08T10:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-08T10:00:00.000000Z"),
 *     @OA\Property(property="user", type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="John Doe")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="SupportTicket",
 *     type="object",
 *     title="Support Ticket",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="ticket_number", type="string", example="TKT-68BE5273E3447"),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="assigned_to", type="integer", nullable=true, example=null),
 *     @OA\Property(property="subject", type="string", example="Login Issue"),
 *     @OA\Property(property="message", type="string", example="I'm having trouble accessing my account"),
 *     @OA\Property(property="status", type="string", enum={"open", "in_progress", "resolved", "closed"}, example="open"),
 *     @OA\Property(property="priority", type="string", enum={"low", "medium", "high", "urgent"}, example="medium"),
 *     @OA\Property(property="category", type="string", enum={"technical", "billing", "general", "booking", "account"}, example="technical"),
 *     @OA\Property(property="attachments", type="array", @OA\Items(type="string"), nullable=true, example=null),
 *     @OA\Property(property="resolved_at", type="string", format="date-time", nullable=true, example=null),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-08T10:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-08T10:00:00.000000Z"),
 *     @OA\Property(property="user", type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="John Doe")
 *     )
 * )
 */