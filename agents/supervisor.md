# agents/supervisor.md 

CRUD Supervisor is used to perform operations on the **user** table with the **Supervisor** role, along with the **supervisor** table. The **supervisor** table is therefore highly dependent on the user table.

> Before reading this document, make sure you have already read **AGENTS.md** to understand the context.

---

## Access Rights
* **Create**: Only roles above Supervisor can perform full Create operations.  
* **Read**: Only the Supervisor role and the supervisor themselves can view their own data; they cannot view other Supervisors. Roles above Supervisor can perform full Read operations.  
* **Update**: Only the Supervisor role and the supervisor themselves can update their own data; they cannot update other Supervisors. Roles above Supervisor can perform full Update operations.  
* **Delete**: Only the Supervisor role and the supervisor themselves can delete their own data; they cannot delete other Supervisors. Roles above Supervisor can perform full Delete operations.  

---

## List – `/supervisors/`

1. Page title: **Supervisors**.  

2. **Search Input**  
   * Search records based on all columns displayed in the table (no 10-record limit).  
   * Search runs automatically whenever the input changes.  
   * A **Search** button is available if the automatic search does not work.  

3. **Filter** (sidebar opens from the right after clicking the filter button):  
   * Title: **Filter Supervisors**  
   * **X** button to close the sidebar  
   * Inputs:  
     * Name (text)  
     * Email (text)  
     * Phone (text)  
     * Is Email Verified? (radio: True / False)  
     * Email Verified At (date)  
     * Department (text)  
     * Have notes? (radio: True / False)  
     * Have photo? (radio: True / False)  
   * **Reset** button to clear filters  
   * **Apply** button to apply filters  
   * Note: Filters can be combined for more specific search results.  

4. **Table** with columns: Name, Email, Phone, Department.  

5. Display **10 records per page**, with **Next** and **Back** navigation.  

6. Display the total number of supervisors.  

7. Display page information in the format: `Page X out of N` (X = current page, N = total pages).  

---

## Create – `/supervisors/create/`

1. Page title: **Create Supervisor**.  

2. Inputs:  
   * Name (Text)  
   * Email (Email)  
   * Phone (Number)  
   * Password (Password)  
   * Supervisor Number (Number)  
   * Department (Text)  
   * Notes (TextArea)  
   * Photo (Text)  

3. Notes:  
   * ID is not an input field.  
   * User ID is not an input field.  
   * Role is assigned automatically.  
   * `email_verified_at` is still TBD.  

4. **Cancel** button to go back.  

5. **Save** button to store the new data.  

---

## Read – `/supervisors/[id]/read/`

Supervisor details are displayed as:  
* Photo: {value}  
* Name: {value}  
* Email: {value}  
* Phone: {value}  
* Email Verified At: {value} (if empty, display **False**)  
* Supervisor Number: {value}  
* Department: {value}  
* Notes: {value}  

---

## Update – `/supervisors/[id]/update/`

1. Page title: **Update Supervisor**.  

2. Inputs:  
   * Name (Text)  
   * Email (Email)  
   * Phone (Number)  
   * Password (Password)  
   * Supervisor Number (Number)  
   * Department (Text)  
   * Notes (TextArea)  
   * Photo (Text)  

3. Notes:  
   * ID is not an input field.  
   * User ID is not an input field.  
   * Role is assigned automatically.  
   * `email_verified_at` is still TBD.  
   * All inputs have default values from the database, except Password.  
   * If Password is not changed, the old value is not overwritten.  

4. **Cancel** button to go back.  

5. **Save** button to store the changes.  

---

## Delete

Delete records using the **Delete** button in the table at the `/supervisors/` endpoint.  

---
