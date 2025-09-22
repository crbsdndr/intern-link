# AGENTS.md

This document serves as the basic key for Agents to understand what I, as the user, expect from the application being developed. By reading this file, every action you take is expected to be more relevant, accurate, and context-appropriate.

I repeat: **“This document is not an instruction manual, but a reference for relevance. Instructions are only given through prompts.”**

**Internish** is an application designed to help teachers manage their students’ internship activities in industry. It is intended for schools that require internships as part of the graduation requirements.

The Internish application fully relies on AI Agents. You have full control, but:

* Do not add anything that is not requested in the prompt or in specific AGENTS files.
* Do not be overly “initiative” beyond the given instructions.

---

### Features (grouped into three categories):

**Foundation**

1. Register
2. Login
3. Logout
4. Security

**Users**

1. CRUD Developer
2. CRUD Admin
3. CRUD Supervisors
4. CRUD Student
   *Note: The order reflects the role hierarchy.*

**Utilities**

1. CRUD Application
2. CRUD Internship
3. CRUD Monitoring

---

Check the files in the **migrations** folder to view the database structure.
*Note: If something needs to be added, changed, or removed in the database structure or views, you may modify it—but always pay attention to and fix the impact of those changes on files that depend on the previous structure, and ensure transparency.*

Check the files in the **Agents** folder for full details on each CRUD. This folder will contain:

* Required inputs for Create (including Role conditions).
* Required columns to display in View (including Role conditions).
* Required inputs for Edit (including Role conditions).
* Delete flow (may require Role).
* Detailed access rights by Role.
* Other supporting information.

---

### Additional Dependencies:

* **Tom Select**: Enables search in dropdown values for easier use.
  *Note: Apply this to all dropdowns except when the values are predictable (hardcoded) and very few.*

---

### Suggestions:

* Learn to write and use reusable code. If it’s not feasible → leave it.
* UI/UX must remain consistent in similar contexts. For example, the Save button in the Create page for both Student and Application must have the same color and border radius.
* Use English throughout the project unless explicitly instructed to use another language.
* When you plan to implement something, always look at other systems that have the same context. Perhaps those systems have already implemented it. That way, you can simply follow the format and UI. This will ensure greater consistency.

### Danger:
* When implementing something, always follow the security guidelines in file agents/security.md