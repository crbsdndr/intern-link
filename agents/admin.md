# agents/admin.md

CRUD Admin is used to perform operations on the **user** table with the **Admin** role.

> Before reading this document, make sure you have already read **AGENTS.md** to understand the context.

---

## Access Rights

* **Create**: Only roles above admin can perform full Create operations.  
* **Read**: Only the admin role and the admin themselves can view their own data; they cannot view other admins. Roles above admin can perform full Read operations.  
* **Update**: Only the admin role and the admin themselves can update their own data; they cannot update other admins. Roles above admin can perform full Update operations.  
* **Delete**: Only the admin role and the admin themselves can delete their own data; they cannot delete other admins. Roles above admin can perform full Delete operations.  

---

## List – `/admins/`

1. Page title: **Admins**.

2. **Search Input**  
   * Search across all displayed table columns (no 10-record limit).  
   * Search runs automatically whenever the input changes.  
   * A **Search** button is provided in case auto-search does not work.

3. **Button filter**
   * This button only appears when applying filters.
   * The number of buttons corresponds to how many filters are applied.
   * Button format: “{filter name}: {filter value}”

4. **Filter** (sidebar opens from the right when the filter button is clicked):  
   * Title: **Filter Admins**  
   * **X** button to close the sidebar  
   * Inputs:  
     * Name (text)  
     * Email (text)  
     * Phone (text)  
     * Is Email Verified? (radio: True / False / Any)  
     * Email Verified At (date)  
   * **Reset** button to clear filters  
   * **Apply** button to apply filters  
   * Note: Filters can be combined for more specific search results.  

5. **Table** with columns: Name, Email, Phone.  
Notes: Anticipate if the table width exceeds the screen width due to its content. By adding a horizontal scroll bar below the table if it exceeds the screen width. Don't force the table to be long and wide explicitly, but adjust it to the content.

6. Display **10 records per page**, with **Next** and **Back** navigation.  

7. Display the total number of admins.  

8. Display page information in the format: `Page X out of N` (X = current page, N = total pages).  

---

## Create – `/admins/create/`

1. Page title: **Create Admin**.  

2. Inputs:  
   * Name (Text)  
   * Email (Email)  
   * Phone (Number)  
   * Password (Password)
   * Cancel (Button)  
   * Save (Button)

3. Notes:  
   * ID is not an input field.  
   * Role is assigned automatically.
   * `email_verified_at` is still TBD.  

4. **Cancel** button to go back.  

5. **Save** button to save the new data.  

---

## Read – `/admins/[id]/read/`

Admin details are displayed as:  
* Name: {value}  
* Email: {value}  
* Phone: {value}  
* Email Verified At: {value} (if empty, display **False**)  

---

## Update – `/admins/[id]/update/`

1. Page title: **Update Admin**.  

2. Inputs:  
   * Name (Text)
   * Email (Email)
   * Phone (Number)  
   * Password (Password)
   * Cancel (Button)
   * Save (Button)

3. Notes:  
   * ID is not an input field.  
   * Role is assigned automatically.  
   * `email_verified_at` is still TBD.  
   * All inputs have default values from the database, except Password.  
   * If Password is not changed, the old value is not overwritten.  

4. **Cancel** button to go back.  

5. **Save** button to save changes.  

---

## Delete

Delete records using the **Delete** button in the table at the `/admins/` endpoint.  

---