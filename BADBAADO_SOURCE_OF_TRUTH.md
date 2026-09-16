# BADBAADO — MASTER SOURCE OF TRUTH
## Product, UX, Architecture, Functional Specification & Development Rules

**Document status:** Master source of truth  
**Purpose:** This document is the authoritative reference for rebuilding and developing BADBAADO.  
**Rule:** When implementation, design, or AI-generated code conflicts with this document, this document takes precedence unless the project owner explicitly changes the decision.

---

# 1. PROJECT IDENTITY

## Product Name

**BADBAADO**

## Core Product Title

**Smart Inter-Hospital Referral & Emergency Pre-Alert Triage Gateway**

## Primary Motto

**Connecting Hospitals, Connecting Care**

## Supporting Product Principle

**Information arrives before the patient.**

## Core Human-Centered Principle

**The patient should not be the messenger between hospitals.**

## One-Sentence Description

BADBAADO is a digital coordination layer that connects hospitals during patient referrals by allowing referral information, emergency pre-alerts, communication, status updates, and handover information to move between healthcare facilities before and during transfer.

---

# 2. WHAT BADBAADO IS

BADBAADO focuses on the space **between hospitals**.

It helps a referring hospital communicate structured referral information to a receiving hospital, allows the receiving team to review and respond, supports emergency pre-alerts, keeps both sides aware of referral status, and maintains an auditable history of what happened.

BADBAADO is a **coordination and referral platform**, not a replacement for a hospital's entire information system.

The system should make the referral journey clearer, faster, more organized, and less dependent on informal communication.

---

# 3. WHAT BADBAADO IS NOT

Do not silently transform BADBAADO into any of the following:

- Full Hospital Information System (HIS)
- Full Electronic Medical Record (EMR)
- Hospital billing system
- Pharmacy management system
- Laboratory management system
- Ambulance dispatch platform
- GPS/vehicle tracking system
- General appointment booking platform
- AI doctor
- AI diagnostic system
- Autonomous clinical decision maker
- Clinical replacement for doctors or nurses
- Generic SaaS dashboard with unrelated hospital features

Future integrations may exist, but the MVP must remain centered on **inter-hospital referral and emergency pre-alert coordination**.

---

# 4. PRIMARY PROBLEM

When a patient needs to move from one hospital to another, the receiving team may not have the necessary information before the patient arrives.

The patient or family may end up carrying information physically or repeating the same story. Communication can be fragmented across calls, messages, paper documents, or informal channels.

This creates a coordination gap:

**The patient may arrive before the information does.**

BADBAADO addresses that gap by moving relevant referral information ahead of the patient.

---

# 5. CORE SOLUTION

BADBAADO provides a structured digital referral workflow:

**REFERRAL → PRE-ALERT → REVIEW → ACCEPT → TRANSFER → ARRIVAL → COMPLETION**

The platform provides:

- Structured referrals
- Emergency pre-alerts
- Receiving-hospital review
- Accept/reject/request-information actions
- Referral status tracking
- Secure hospital-to-hospital communication
- Notifications
- Referral history
- Audit logs
- AI-assisted urgency suggestion
- Role-based access
- Hospital-level data isolation

---

# 6. CORE REFERRAL LIFECYCLE

The authoritative backend lifecycle is:

**DRAFT → SENT → RECEIVED → UNDER_REVIEW → ACCEPTED → TRANSFER_IN_PROGRESS → ARRIVED → COMPLETED**

Alternative terminal/exception states:

- REJECTED
- CANCELLED

## State meanings

### DRAFT
Referral has been started but has not been submitted.

### SENT
Referring hospital has submitted the referral to the selected receiving hospital.

### RECEIVED
The receiving hospital has received the referral in its system.

### UNDER_REVIEW
A receiving healthcare professional/referral coordinator is actively reviewing the referral.

### ACCEPTED
The receiving hospital has accepted the referral.

### TRANSFER_IN_PROGRESS
The patient transfer is actively underway.

### ARRIVED
The patient has arrived at the receiving hospital.

### COMPLETED
The referral/handover process has been completed.

### REJECTED
The receiving hospital has declined the referral. A reason is required.

### CANCELLED
The referral was cancelled by an authorized user according to the business rules.

## State integrity

The backend must enforce valid state transitions.

Do not allow arbitrary status changes merely because a frontend sends a different status.

Example of an invalid transition:

**COMPLETED → DRAFT**

The API must reject invalid transitions.

Every important transition must be audit logged.

---

# 7. USER ROLES

## HEALTHCARE_WORKER

Can:

- Log in
- View information permitted for their hospital
- Create referrals
- Save referral drafts
- Submit referrals
- View their hospital's relevant referrals
- View referral details
- Send/view referral messages
- View referral status
- Participate in referral communication

Must not:

- Access another hospital's private referrals without explicit system authorization
- Change administrative settings
- Manage system-wide users/hospitals
- Override backend authorization

## REFERRAL_COORDINATOR

Can:

- Monitor referrals
- Monitor transfer progress
- Coordinate communication
- Review incoming referrals as permitted
- Track referral status
- Help coordinate referral actions

## HOSPITAL_ADMIN

Can:

- Manage users belonging to their hospital
- View hospital-level activity
- Manage appropriate hospital permissions
- Monitor hospital referrals/activity

Must remain restricted to their hospital unless a system-level permission explicitly allows otherwise.

## SYSTEM_ADMIN

Can:

- Manage hospitals
- Manage system users
- Configure platform-level settings
- Monitor system-wide activity
- Review system-level audit/activity information
- Manage platform configuration

System administrators are the only role intended to have system-wide administrative visibility.

---

# 8. HOSPITAL DATA ISOLATION

Hospital isolation is a core security requirement.

A user belonging to Hospital A must not be able to retrieve Hospital B's private referral data simply by changing an ID in a URL or API request.

Authorization must be enforced server-side.

Do not rely on:

- Hidden buttons
- Frontend route guards alone
- JavaScript checks
- UI visibility alone

The backend must determine whether the authenticated user is allowed to access the requested resource.

---

# 9. CORE USER JOURNEY

## Referring hospital

1. Healthcare worker logs in.
2. User opens the referral workflow.
3. User selects the receiving hospital.
4. User enters required referral information.
5. User indicates whether the referral requires emergency pre-alert handling.
6. AI may provide an urgency suggestion.
7. Healthcare professional reviews the information and suggestion.
8. User submits the referral.
9. Receiving hospital receives notification/pre-alert.
10. Referring hospital can monitor the referral lifecycle.
11. Communication can continue through the referral.
12. Transfer status changes as the process progresses.
13. Receiving hospital confirms arrival.
14. Referral is completed.

## Receiving hospital

1. Authorized user logs in.
2. Incoming referral appears in the appropriate queue/dashboard.
3. Emergency/pre-alert information is visually prioritized.
4. User opens referral details.
5. User reviews clinical/referral information.
6. User may:
   - Accept
   - Reject with reason
   - Request additional information
7. Acceptance starts the transfer coordination phase.
8. Messages and notifications keep relevant users informed.
9. Arrival is recorded.
10. Referral becomes completed when the workflow is finished.

---

# 10. REFERRAL INFORMATION

The referral model should contain enough structured information to support coordination without collecting unnecessary data.

Potential categories:

## Patient information

Use minimal information required by the prototype.

Possible fields:

- Patient identifier/reference
- Name where appropriate for the prototype
- Age/date of birth as appropriate
- Sex where relevant
- Contact/identifier information only when required

## Clinical information

Possible structured fields:

- Referral reason
- Symptoms
- Relevant observations/vitals
- Consciousness status
- Trauma indicator
- Existing relevant conditions
- Current treatment/interventions
- Relevant notes

## Transfer information

- Referring hospital
- Receiving hospital
- Referring healthcare worker
- Referral coordinator where applicable
- Transfer status
- Emergency/pre-alert flag
- Additional notes

The prototype must use clearly labeled **fictional/demo data** rather than real patient data.

---

# 11. EMERGENCY PRE-ALERT

The pre-alert exists to communicate that the receiving hospital should prepare before the patient arrives.

It should provide clear information without becoming a flashing alarm interface.

The receiving dashboard should make urgent referrals visually obvious through:

- Strong hierarchy
- Clear labels
- Restrained urgency colors
- Status badges
- Clear action buttons
- Relevant timing/status information

Avoid:

- Flashing screens
- Excessive red
- Alarm-like animation
- Gaming/SOC aesthetics
- Unnecessary sound effects

Urgency must be **obvious but calm**.

---

# 12. AI-ASSISTED URGENCY SUGGESTION

AI is strictly assistive.

The system may provide:

**AI-assisted urgency suggestion: HIGH**

with:

- Urgency
- Confidence
- Reason/explanation

Conceptually:

```json
{
  "urgency": "HIGH",
  "confidence": 0.87,
  "reason": "Structured observations indicate elevated urgency."
}
```

The exact implementation can change as long as the behavior remains consistent with this specification.

## AI inputs

A prototype may use structured inputs such as:

- Heart rate
- Blood pressure
- SpO2
- Temperature
- Symptoms
- Referral reason
- Consciousness
- Trauma indicator

## AI restrictions

The AI must NOT:

- Diagnose
- Claim clinical certainty
- Replace healthcare professionals
- Automatically make the final urgency decision
- Present itself as a doctor
- Use language such as "AI doctor"
- Make itself visually dominant over human decisions
- Make clinical-grade claims for a prototype

The final urgency classification/decision belongs to a healthcare professional.

The UI should clearly distinguish:

- Clinical information
- System-generated information
- AI-generated suggestion
- Human decision

---

# 13. TECHNICAL STACK

The currently selected stack is:

## Backend

**PHP 8.x + Laravel**

Laravel is the primary backend framework.

## API

**Laravel REST API**

The application should expose clean RESTful endpoints for the frontend and future integrations.

## Authentication

**Laravel Sanctum**

## Authorization

Laravel authorization mechanisms such as:

- Policies
- Gates
- Role/permission architecture

Authorization must be enforced on the backend.

## Database

**MySQL**

Use Laravel migrations, models, relationships, validation, and ORM/database features appropriately.

## Frontend

- HTML5
- CSS3
- JavaScript
- Bootstrap
- Fetch API / AJAX

React is not required for the current implementation.

Do not switch the backend to ASP.NET, Django, Node.js, or another framework unless the project owner explicitly reopens the technology decision.

## AI

**Python-based AI component/service**

The AI component should remain logically separated from the core Laravel application.

The Laravel backend can communicate with the AI component through a controlled interface/API.

## Local development

**XAMPP**

Do not replace XAMPP unnecessarily.

## Version control

**Git**

---

# 14. ARCHITECTURE

The intended conceptual architecture is:

```text
                 BADBAADO USERS
                       |
                       v
             Web Application / UI
          HTML + CSS + JS + Bootstrap
                       |
                       v
                Laravel REST API
                       |
       +---------------+----------------+
       |               |                |
       v               v                v
 Authentication    Referral Logic   Notifications
 Authorization     Messaging        Audit Logging
 Hospital Logic    Status Flow      Other Services
       |               |                |
       +---------------+----------------+
                       |
                       v
                    MySQL
                       |
                       |
                       v
             Python AI Component
          AI-Assisted Urgency Suggestion
```

Keep the architecture understandable.

Do not introduce unnecessary microservices, containers, orchestration, or enterprise complexity simply to make the diagram look impressive.

---

# 15. API STRUCTURE

A clean REST structure may use:

```text
/api/auth
/api/users
/api/hospitals
/api/referrals
/api/messages
/api/notifications
/api/ai
/api/audit
```

Exact endpoint names can be refined during implementation, but the API should remain logically organized.

Examples:

```text
POST   /api/auth/login
POST   /api/auth/logout
GET    /api/referrals
POST   /api/referrals
GET    /api/referrals/{id}
PATCH  /api/referrals/{id}
POST   /api/referrals/{id}/submit
POST   /api/referrals/{id}/accept
POST   /api/referrals/{id}/reject
POST   /api/referrals/{id}/request-information
POST   /api/referrals/{id}/messages
GET    /api/referrals/{id}/messages
```

Do not implement an endpoint merely because it appears in an example. Its authorization and business rules must be defined.

---

# 16. DATABASE CONCEPTS

Core relational entities include:

- users
- roles
- hospitals
- patients/minimal patient references
- referrals
- messages
- notifications
- audit_logs

Possible relationships:

```text
Hospital 1 ─── * Users
Hospital 1 ─── * Referrals (as referring hospital)
Hospital 1 ─── * Referrals (as receiving hospital)
Referral 1 ─── * Messages
Referral 1 ─── * Audit Logs
User 1 ─── * Audit Logs
User 1 ─── * Notifications
```

Database integrity should be enforced through appropriate:

- Foreign keys
- Indexes
- Unique constraints
- Nullability rules
- Validation
- Status constraints where practical

Do not store passwords as plaintext.

---

# 17. SECURITY REQUIREMENTS

Security is a core part of BADBAADO, not an optional later addition.

Required principles:

## Authentication

Users must authenticate before accessing protected system functionality.

## Authorization

Backend authorization must determine what each user can access.

## Hospital isolation

Users must be restricted to appropriate hospital data.

## Password security

Use secure password hashing such as Laravel's supported secure mechanisms.

Never store plaintext passwords.

## Input validation

Validate all user-controlled input server-side.

## SQL injection protection

Use Laravel's ORM/query builder/parameterized mechanisms appropriately.

Do not concatenate untrusted input into SQL queries.

## XSS protection

Escape/sanitize user-controlled output appropriately.

## CSRF

Use Laravel's appropriate CSRF protections for stateful web requests.

## Rate limiting

Sensitive endpoints such as authentication should have reasonable rate limiting.

## Audit logging

Important actions should be recorded.

## Data minimization

Core principle:

**Collect what is necessary. Protect what is collected.**

## Demo data

Use fictional/demo patient data for the competition prototype.

Do not present fictional data as real clinical records.

---

# 18. AUDIT LOGGING

Important actions should generate audit records.

Examples:

- Login
- Referral creation
- Referral submission
- Referral received
- Referral opened/reviewed
- Referral accepted
- Referral rejected
- Additional information requested
- Status changes
- Messages/actions where appropriate
- Arrival
- Completion
- Administrative changes

A useful audit record can conceptually contain:

```text
id
user_id
action
entity_type
entity_id
metadata
timestamp
```

Do not expose sensitive audit information to ordinary users unless their role permits it.

---

# 19. MESSAGING

Messaging belongs to the referral context.

Users should be able to communicate about a referral without losing the connection between the message and the referral.

The UI should make clear:

- Who sent the message
- When it was sent
- Which referral it belongs to
- Whether there are unread messages

Do not turn this into a generic social/chat platform.

---

# 20. NOTIFICATIONS

Notifications should communicate important events.

Examples:

- New referral received
- Emergency pre-alert received
- Referral accepted
- Referral rejected
- Additional information requested
- Transfer status changed
- Patient arrived
- Referral completed
- New relevant message

For the MVP, in-app notifications are sufficient.

The architecture should allow other delivery methods later without requiring the whole referral system to be redesigned.

---

# 21. UI/UX DESIGN SYSTEM

## Overall feeling

BADBAADO should feel:

- Trustworthy
- Calm
- Professional
- Modern
- Human-centered
- Reliable
- Accessible
- Serious
- Technological without looking futuristic

The visual impression should be:

**Serious healthcare infrastructure powered by technology.**

---

# 22. PRIMARY DESIGN LANGUAGE

Use a coherent combination of:

### Minimalism

Foundation of the interface:

- Clean layouts
- Generous but purposeful spacing
- Strong hierarchy
- Few unnecessary decorative elements
- Clear typography

### Swiss / International design principles

Use:

- Grid structure
- Strong alignment
- Consistent spacing
- Typography-led hierarchy
- Functional visual organization
- Deliberate composition

### Editorial information hierarchy

Information should feel intentionally arranged.

Important data should be visually prioritized without creating clutter.

### Data-interface / command-center influence

Use carefully for:

- Status monitoring
- Referral queues
- Timelines
- Activity
- Urgency
- Operational summaries

This must NOT become a hacker/SOC visual style.

---

# 23. LIMITED USE OF TREND STYLES

Glassmorphism and Neumorphism may be used only as subtle supporting techniques where they genuinely improve hierarchy.

Do not use them as the primary visual identity.

Do not combine multiple trendy styles randomly.

Do not create a collection of unrelated aesthetic experiments.

BADBAADO needs one coherent design system.

---

# 24. VISUAL CONCEPT: CONNECTION

The central visual idea is:

**CONNECTION**

Visual language can suggest:

- Two hospitals connected by a digital pathway
- Information moving between facilities
- A referral traveling from one hospital to another
- Continuity of care
- A bridge between healthcare teams

The connection concept should feel like healthcare infrastructure, not a telecom/network diagram.

---

# 25. DESIGN ELEMENTS TO AVOID

Avoid:

- Generic medical crosses everywhere
- Stock hospital imagery
- Pharmacy aesthetics
- Ambulance-centric visuals
- Fitness-app aesthetics
- Neon
- Excessive gradients
- Excessive glassmorphism
- Excessive neumorphism
- Excessive pill-shaped controls
- Excessive rounded cards
- Cluttered dashboards
- Tiny text
- Excessive animation
- Flashing alerts
- Sci-fi interfaces
- Robot doctors
- AI brains/robots as generic AI imagery
- Fake medical claims
- Decorative elements that compete with information

---

# 26. COLOR AND URGENCY

Exact brand colors can be finalized from the approved visual brand/logo when available.

Until then, use a restrained, professional palette.

Urgency should be communicated through:

- Hierarchy
- Labels
- Status
- Controlled color
- Position
- Typography
- Clear actions

Do not make the interface look like an alarm system.

Normal workflows should feel calm.

Emergency information should be immediately recognizable.

---

# 27. TYPOGRAPHY

Typography should prioritize:

- Readability
- Accessibility
- Clear hierarchy
- Professional appearance
- Strong distinction between headings, metadata, labels, and values

Avoid tiny typography.

Do not use decorative fonts.

The exact font can be selected during implementation, but it must fit the established visual language.

---

# 28. NAVIGATION

The application should have a consistent navigation system.

Possible primary destinations:

- Dashboard
- Incoming Referrals
- My/Outgoing Referrals
- Messages
- Notifications
- Referral History
- Administration where permitted

Navigation must change according to role where necessary.

Users should not see administrative navigation they cannot access.

---

# 29. DASHBOARD

The dashboard should help a healthcare worker understand what requires attention.

Possible summary areas:

- NEW
- URGENT
- CRITICAL
- ACTIVE TRANSFERS

Then an organized referral list.

The dashboard should prioritize operational information rather than decorative statistics.

Avoid filling the screen with meaningless analytics.

---

# 30. REFERRAL LIST

Referral lists should make it easy to understand:

- Referral identifier
- Referring hospital
- Receiving hospital
- Urgency
- Current status
- Time/date
- Relevant patient reference
- Last activity

Use clear status badges.

Make urgent referrals visually distinguishable.

Provide appropriate filtering/search without turning the interface into a complicated enterprise table.

---

# 31. REFERRAL DETAILS

The referral details page is one of the most important screens.

It should clearly separate:

### Referral overview

- Referral ID
- Hospitals
- Status
- Urgency
- Created/submitted time

### Patient information

Minimal relevant patient information.

### Clinical/referral information

Symptoms, referral reason, observations, relevant notes.

### AI suggestion

Clearly labeled as AI-generated and assistive.

### Human decision

Clearly distinguish the healthcare professional's decision from the AI suggestion.

### Timeline

Show referral lifecycle events in chronological order.

### Communication

Referral-specific messages.

### Actions

Only actions authorized for the current user and current referral state.

---

# 32. REFERRAL CREATION

The referral form should be structured and easy to complete.

Avoid an enormous wall of fields.

Group fields logically.

Example sections:

1. Receiving hospital
2. Patient information
3. Referral reason
4. Clinical information
5. Observations
6. Transfer/emergency information
7. Additional notes
8. AI-assisted urgency suggestion
9. Review and submit

Validation errors should be specific and understandable.

---

# 33. AI UI

AI should appear as a supporting information layer.

Example:

**AI-assisted urgency suggestion**

**HIGH**

**Confidence: 87%**

Reason:

“Structured observations indicate elevated urgency.”

Then clearly show:

**Final urgency decision: Healthcare professional**

Do not visually make the AI card larger or more authoritative than the human decision area.

---

# 34. REFERRAL TIMELINE

The timeline should communicate the journey:

```text
Referral created
      ↓
Sent
      ↓
Received
      ↓
Under review
      ↓
Accepted
      ↓
Transfer in progress
      ↓
Arrived
      ↓
Completed
```

Each event can show:

- Status
- Timestamp
- Actor where appropriate
- Relevant action

This is an important visual representation of the product's core value.

---

# 35. MOBILE UX

The system should work on smaller screens.

Mobile is especially important because healthcare workers may access information while moving.

Prioritize:

- Large touch targets
- Readable text
- Clear actions
- Minimal horizontal scrolling
- Important information near the top
- Calm but obvious urgency
- Easy referral status viewing

Do not simply shrink the desktop dashboard.

---

# 36. STATES EVERY IMPORTANT UI COMPONENT SHOULD HANDLE

Do not build only the happy path.

Consider:

- Loading
- Empty state
- Error
- Validation error
- Success
- Unauthorized
- Forbidden
- Not found
- Offline/network failure where relevant
- No notifications
- No referrals
- No messages
- Long content
- Mobile layout

---

# 37. PUBLIC WEBSITE VS APPLICATION

BADBAADO may be structured as two related web experiences:

## Public-facing website

Purpose:

- Explain what BADBAADO is
- Explain the problem
- Explain the solution
- Present features
- Present the team/project
- Present competition/demo information
- Communicate the brand and mission

## Authenticated application

Purpose:

- Login
- Dashboard
- Referrals
- Pre-alerts
- Messaging
- Notifications
- Referral tracking
- Administration according to role

Exact domains/subdomains are **not locked** by this document.

The important architectural distinction is:

**Public informational experience ≠ authenticated operational system.**

---

# 38. EXISTING FRIENDS' VERSION — RECOVERY RULE

The current/recovered friends' application is considered a:

**VISUAL/UI STARTING POINT ONLY.**

It is NOT authoritative for:

- Architecture
- Backend behavior
- Database design
- API design
- Security
- Business logic
- Referral lifecycle
- User permissions
- Technology decisions
- AI behavior

The existing version may contain useful:

- Layouts
- Components
- Visual styling
- Screens
- Navigation ideas
- Branding
- Visual assets

Preserve useful visual work when it aligns with this document.

Rewrite, replace, or restructure implementation where necessary.

Do not allow existing fake buttons, placeholder data, mock workflows, or incorrect technology decisions to define how BADBAADO actually works.

---

# 39. DEVELOPMENT PHILOSOPHY

The project owner wants to understand and test the system while developing it.

Therefore:

- Work incrementally.
- Build real functionality.
- Avoid fake buttons where practical.
- Avoid giant untestable code dumps.
- Explain important architectural decisions.
- Provide concrete test steps after meaningful features.
- Keep code understandable.
- Use conventional Laravel structure.
- Avoid unnecessary dependencies.
- Avoid over-engineering.
- Keep the competition deadline in mind.
- Prioritize the end-to-end referral journey.

The goal is not to build every imaginable feature.

The goal is to build a **coherent, functional, defensible BADBAADO prototype**.

---

# 40. MVP PRIORITY

The competition MVP should prioritize:

1. Authentication
2. Hospital accounts
3. Role-based access
4. Hospital data isolation
5. Referral creation
6. Referral submission
7. Incoming referral dashboard
8. Referral status tracking
9. Emergency pre-alert
10. Notifications
11. Referral messaging
12. Accept/reject/request information
13. AI-assisted urgency suggestion
14. Referral history
15. Audit logging
16. Polished responsive UI
17. End-to-end demo flow

Do not sacrifice the core referral workflow to add unrelated features.

---

# 41. END-TO-END DEMO SCENARIO

The competition demo should be able to demonstrate a complete story:

```text
Hospital A
   |
   | creates referral
   v
BADBAADO
   |
   | sends referral + pre-alert
   v
Hospital B
   |
   | reviews
   v
AI suggestion shown as assistance
   |
   | healthcare professional decides
   v
ACCEPTED
   |
   v
TRANSFER IN PROGRESS
   |
   v
ARRIVED
   |
   v
COMPLETED
```

Throughout the process:

- Notifications appear
- Messages can be exchanged
- Timeline updates
- Audit logs record major actions
- Permissions remain enforced

This is the core story of BADBAADO.

---

# 42. FUTURE FEATURES

Possible future expansion includes:

- Ambulance integration
- Hospital capacity information
- Specialist availability
- GPS/arrival estimation
- Health-system integrations
- Multi-hospital networks
- Advanced analytics
- External notification channels
- Interoperability standards
- More sophisticated AI assistance

These are future possibilities, not requirements for the MVP.

---

# 43. UNDEFINED ITEMS

The following may still be decided during implementation:

- Exact referral ID format
- Exact AI model
- Exact AI deployment mechanism
- Polling vs WebSockets
- Exact notification delivery methods
- Exact session configuration
- Exact brand hex codes if the approved logo/palette has not been inspected
- Exact domain names
- Exact font family

When choosing an undefined item:

1. Prefer the simplest robust solution.
2. Maintain consistency with this document.
3. Do not introduce unnecessary complexity.
4. Tell the project owner what was decided.
5. Do not silently turn an undefined choice into a major architectural change.

---

# 44. SOURCE-OF-TRUTH RULE

When uncertain, the AI/developer must return to this document.

Priority order:

**1. Explicit decisions in this document**

**2. Other approved BADBAADO project documentation**

**3. Existing implementation only as a visual/reference source**

**4. Reasonable engineering judgment for genuinely undefined details**

Never reverse this order.

---

# 45. CONFLICT RULE

If existing code conflicts with this document:

**The document wins.**

If an existing design conflicts with the locked UI/UX direction:

**The design direction wins.**

If a generated feature conflicts with the MVP scope:

**The MVP scope wins.**

If AI-generated output conflicts with the AI restrictions:

**The AI restrictions win.**

If the AI does not know:

**Do not invent. Return to this document.**

---

# 46. DEVELOPMENT COMMANDS

The following shorthand commands may be used during development.

## PLAN

Explain the intended implementation and affected files/components. Do not code yet.

## BUILD

Implement the agreed component.

## EXPLAIN

Explain the current implementation in beginner-friendly technical terms.

## TEST

Give exact steps/commands to test the current feature.

## DEBUG

Focus on diagnosing the current problem. Avoid unrelated refactoring.

## REVIEW

Review architecture, code quality, bugs, security, and BADBAADO alignment.

## SECURITY

Perform a security-focused review.

## DATABASE

Focus on schema, relationships, migrations, indexes, constraints, and integrity.

## API

Focus on endpoints, request/response behavior, validation, authentication, and authorization.

## UI

Focus on interface/UX and the locked BADBAADO design language.

## ARCHITECTURE

Step back and assess the current system architecture.

## SCOPE

Determine whether a requested feature belongs in the current MVP or is scope creep.

## SOURCE

Return to this document and use it as the source of truth before continuing.

## STATUS

Summarize completed work, current work, remaining work, and known issues.

## COMMIT

Prepare a suggested Git commit and message. Do not commit unless explicitly authorized.

---

# 47. AI CODING AGENT RULES

When working on BADBAADO:

- Read this file before major implementation.
- Do not assume previous conversation memory exists.
- Inspect the actual repository.
- Do not assume existing code is correct.
- Do not delete useful work without understanding it.
- Do not blindly preserve broken implementation.
- Do not change the technology stack without explicit permission.
- Do not create fake functionality and describe it as complete.
- Do not claim security properties that have not been implemented.
- Do not claim clinical validation.
- Do not fabricate real-world hospital data.
- Use fictional/demo data for the prototype.
- Test important functionality.
- Keep the implementation consistent with the referral lifecycle.
- Keep user permissions enforced server-side.
- Keep hospital data isolated.
- Keep AI assistive.
- Keep the interface coherent.

---

# 48. DEFINITION OF DONE

A feature is not considered complete merely because:

- A button exists
- A page looks correct
- Mock data appears
- A form submits visually

A meaningful feature should have, where applicable:

- UI
- Backend logic
- Validation
- Database persistence
- Authorization
- Error handling
- Appropriate audit logging
- Correct status behavior
- Testable behavior
- Responsive presentation

For competition speed, not every feature requires production-level completeness, but the core referral workflow must be genuinely functional.

---

# 49. FINAL PRODUCT PRINCIPLE

Every major design and engineering decision should reinforce the same idea:

**BADBAADO connects hospitals so that information can move before the patient does.**

The product should feel like a bridge between healthcare teams.

Not a generic hospital system.

Not an AI doctor.

Not an ambulance tracker.

Not a decorative dashboard.

**A focused, secure, human-centered inter-hospital coordination system.**

---

# 50. FINAL SOURCE-OF-TRUTH STATEMENT

When developing BADBAADO, remember:

> **The documents define what BADBAADO is.**
>
> **The technology stack defines how we build it.**
>
> **The existing friends' application provides a visual starting point, not the product specification.**
>
> **The implementation must serve the product, not redefine it.**
>
> **When uncertain, come back to this document before inventing a solution.**

**BADBAADO — Connecting Hospitals, Connecting Care.**
