# Database Seeders

## Overview

This document explains all available seeders for testing the karate registration system.

## Base Seeders

### RolesAndPermissionsSeeder
Creates user roles (super-admin, panitia, kontingen) and permissions, plus default users.

**Run:** `php artisan db:seed --class=RolesAndPermissionsSeeder`

**Creates:**
- 3 roles: super-admin, panitia, kontingen
- All permissions
- 3 default users (admin, panitia, kontingen)

### ContingentSeeder
Creates sample contingents from various cities.

**Run:** `php artisan db:seed --class=ContingentSeeder`

**Creates:**
- 6 contingents: Jakarta, Bandung, Surabaya, Semarang, Yogyakarta, Medan
- Links kontingen user to first contingent

### ParticipantSeeder
Creates participants (athletes, coaches, officials) for the first contingent.

**Run:** `php artisan db:seed --class=ParticipantSeeder`

**Creates:**
- 18 athletes (JUNIOR, U21, DEWASA - male and female)
- 3 coaches
- 4 officials

### Contingent6ParticipantSeeder
Creates participants for contingent with ID 6.

**Run:** `php artisan db:seed --class=Contingent6ParticipantSeeder`

**Creates:**
- 18 athletes (JUNIOR, U21, DEWASA - male and female)
- 3 coaches
- 4 officials

### EventSeeder
Creates the main event with categories and subcategories.

**Run:** `php artisan db:seed --class=EventSeeder`

**Creates:**
- 1 main event: "Piala Karate Indonesia 2026"
- 3 additional events for landing page
- Event categories (Open/Festival × JUNIOR/U21/DEWASA)
- Subcategories (KATA/KUMITE for various categories)

### NewEventSeeder
Creates 5 new events with posters.

**Run:** `php artisan db:seed --class=NewEventSeeder`

**Creates:**
- 5 events with posters from agent/img folder
- Events: Piala Presiden, Jakarta Open, Nasional Yunior, Indonesia Festival, Piala KONI

## Registration Flow Seeders

### PaymentSeeder
Creates payments for contingents attending events.

**Run:** `php artisan db:seed --class=PaymentSeeder`

**Creates:**
- 2-3 payments per contingent per event
- Various statuses: pending, verified, rejected
- Sample transfer proof paths

### TeamGroupSeeder
Creates team groups for beregu (team) categories.

**Run:** `php artisan db:seed --class=TeamGroupSeeder`

**Creates:**
- 1-2 teams per contingent per beregu category
- Team names and numbers

### RegistrationSeeder
Creates registrations linking participants to categories.

**Run:** `php artisan db:seed --class=RegistrationSeeder`

**Creates:**
- 1-3 registrations per athlete
- Coach registrations (without subcategory)
- Various statuses: pending_review, verified, rejected
- Links to payments and team groups

### ResultSeeder
Creates results (medals) for verified registrations.

**Run:** `php artisan db:seed --class=ResultSeeder`

**Creates:**
- Results for verified registrations
- Gold, Silver, Bronze medals
- Some participants without medals

## Composite Seeders

### EventRegistrationSeeder
Runs all event-related seeders in the correct order.

**Run:** `php artisan db:seed --class=EventRegistrationSeeder`

**Order:** PaymentSeeder → TeamGroupSeeder → RegistrationSeeder → ResultSeeder

### ParticipantReportSeeder
Creates sample data for participant report testing.

**Run:** `php artisan db:seed --class=ParticipantReportSeeder`

## Running All Seeders

**IMPORTANT: Run in the correct order to maintain foreign key relationships!**

The `DatabaseSeeder` runs seeders in this order:

1. **RolesAndPermissionsSeeder** - Users, Roles, Permissions
2. **ContingentSeeder** - Contingents (6 cities)
3. **ParticipantSeeder** - Participants for contingent 1
4. **Contingent6ParticipantSeeder** - Participants for contingent 6
5. **EventSeeder** - Events with categories & subcategories
6. **NewEventSeeder** - 5 more events with posters
7. **EventRegistrationSeeder** - Payments, TeamGroups, Registrations, Results
8. **ParticipantReportSeeder** - Report test data

To run all seeders in the correct order:

```bash
php artisan db:seed
```

Or fresh database with seeders:

```bash
php artisan migrate:fresh --seed
```

## Individual Testing

For testing specific features:

**Test Event System:**
```bash
php artisan db:seed --class=EventSeeder
php artisan db:seed --class=NewEventSeeder
```

**Test Registration Flow:**
```bash
php artisan db:seed --class=EventRegistrationSeeder
```

**Test Reports:**
```bash
php artisan db:seed --class=ParticipantReportSeeder
```

## Data Relationships

```
Contingent
  ├── Participant (Athletes, Coaches, Officials)
  ├── Payment
  ├── TeamGroup
  └── RegistrationDraft

Event
  ├── EventCategory
  │   └── SubCategory
  ├── Payment
  └── RegistrationDraft

Payment
  └── Registration

Participant
  └── Registration

SubCategory
  ├── TeamGroup
  └── Registration

Registration
  └── Result

TeamGroup
  ├── Registration
  └── RegistrationDraftItem
```
