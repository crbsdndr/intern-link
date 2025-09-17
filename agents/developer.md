# agents/developer.md

CRUD Developer is used to perform operations on the **user** table with the **Developer** role.

> Before reading this document, make sure you have already read **AGENTS.md** to understand the context.

---

## Access Rights

* **Create**: No client role can create new data.  
* **Read**: A developer can only view their own data.  
* **Update**: A developer can only update their own data.  
* **Delete**: A developer can only delete their own data.  

---

## List – `/developers/`

1. Page title: **Developers**.  

2. **Search Input**  
   * Search records based on all columns displayed in the table (no 10-record limit).  
   * Search runs automatically whenever the input changes.  
   * A **Search** button is available if the automatic search does not work.  

3. **Filter** (sidebar opens from the right after clicking the filter button):  
   * Title: **Filter Developer**  
   * **X** button to close the sidebar  
   * Inputs:  
     * Name (text)  
     * Email (text)  
     * Phone (text)  
     * Is Email Verified? (radio: True / False)  
     * Email Verified At (date)  
   * **Reset** button to clear filters  
   * **Apply** button to apply filters  
   * Note: Filters can be combined for more specific search results.  

4. **Table** with columns: Name, Email, Phone.  

5. Display **10 records per page**, with **Next** and **Back** navigation.  

6. Display the total number of developers.  

7. Display page information in the format: `Page X out of N` (X = current page, N = total pages).  

---

## Create – `/developers/create/`

1. Page title: **Create Developer**.  

2. Inputs:  
   * Name (Text)  
   * Email (Email)  
   * Phone (Number)  
   * Password (Password)  

3. Notes:  
   * ID is not an input field.  
   * Role is assigned automatically.  
   * `email_verified_at` is still TBD.  

4. **Cancel** button to go back.  

5. **Save** button to store the new data.  

---

## Read – `/developers/[id]/read/`

Developer details are displayed as:  
* Name: {value}  
* Email: {value}  
* Phone: {value}  
* Email Verified At: {value} (if empty, display **False**)  

---

## Update – `/developers/[id]/update/`

1. Page title: **Update Developer**.  

2. Inputs:  
   * Name (Text)  
   * Email (Email)  
   * Phone (Number)  
   * Password (Password)  

3. Notes:  
   * ID is not an input field.  
   * Role is assigned automatically.  
   * `email_verified_at` is still TBD.  
   * All inputs have default values from the database, except Password.  
   * If Password is not changed, the old value is not overwritten.  

4. **Cancel** button to go back.  

5. **Save** button to store the changes.  

---

## Delete

Delete records using the **Delete** button in the table at the `/developers/` endpoint.  

---