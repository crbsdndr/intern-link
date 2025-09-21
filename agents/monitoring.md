# agents/monitoring.md

The CRUD Monitoring is used to perform operations on the **monitoring_logs** table.

> Before reading this document, make sure you have read **AGENTS.md** to understand the context.

---

## Access Rights
* **Create**: The Student role can't do this. Higher roles can perform Create operation.  
* **Read**: The Student role can only view their own internship. Higher roles can perform full Read operations.  
* **Update**: The Student role can't do this. Higher roles can perform Update operation.  
* **Delete**: The Student role can't do this. Higher roles can perform Delete operation.  

---

## List – `/internships/`
1. Page title: **Internships**

2. **Search Input**  
   * Search across all displayed table columns (no 10-record limit).  
   * Search runs automatically whenever the input changes.  
   * A **Search** button is provided in case auto-search does not work.

3. **Button filter**
   * This button only appears when applying filters.
   * The number of buttons corresponds to how many filters are applied.
   * Button format: “{filter name}: {filter value}”

4. **Filter** (sidebar opens from the right when the filter button is clicked)  
   * Title: **Filter Monitorings**  
   * **X** button to close the sidebar  
   * Inputs:
     * Title (text)
     * Student Name (Dropdown) (Tom Select)
     * Institution Name (Dropdown) (Tom Select)
     * Start Date (Date) (Display the exact log_date selected, but if the user inputs an End Date, it will change to a range.)
     * End Date (Date) (Display the exact log_date selected, but if the user inputs an Start Date, it will change to a range.)
     * Have Content? (radio: True / False / Any)
     * Type (Dropdown) (no Tom Select)
   * **Reset** button to clear filters  
   * **Apply** button to apply filters  
   * Note: Filters can be combined for more specific searches.  

5. **Table** columns: Title, Log Date, Type.
   * Anticipate table width exceeding screen size → add horizontal scroll.  
   * Do not force table to stretch; adjust only as needed to fit content.  

6. Show **10 records per page** with **Next** and **Back** navigation.

7. Show the total number of monitorings.

8. Show page info in the format: `Page X out of N` (X = current page, N = total pages).  

---

## Create – `/monitorings/create/`
1. Page title: **Create Monitoring**  
2. Inputs:  
   * Internship (Dropdown) (Tom Select)
   * "+" (Button) to add a new Internship
   * Additional Internship (Dropdown) (Tom Select) (appears after pressing +)
   * Log Date (Date)
   * Type (Dropdown) (no Tom Select)
   * Content (Dropdown) (TextArea)
   * "Apply this to all company IDs that match the selected Internship (This will not affect existing ones)" (Checkbox) (Doesn't affect existing ones)
   * Cancel (Button)
   * Save (Button)

3. Notes:
   * Internships are displayed as "{Student Name} - {Institution Name}"; the database still stores IDs' Internship.
   * Additional Internship dropdown only shows Internships from the same institution as the first Internship.  
   * The checkbox automatically adds all Internships from the same institution.
   * Do not display Internships already selected in other inputs.  
   * If all Internships from the same institution are already selected → the + button becomes disabled.  

4. **Cancel** button navigates back.  
5. **Save** button stores the new data.  

---

## Read – `/monitorings/[id]/read/`
Monitorings details displayed as:  
* Student Photo: {value}
* Student Name: {value} (click → `/students/[id]/read/`)  
* Student Email: {value}
* Student Phone: {value}
* Student Number: {value}
* National Student Number: {value}
* Student Major: {value}
* Student Class: {value}
* Student Batch: {value}
* Student Notes: {value}
* Institution Photo: {value}
* Institution Name: {value} (click → `/institutions/[id]/read/`)  
* Institution Address: {value}
* Institution City: {value}
* Institution Province: {value}
* Institution Website: {value}
* Institution Industry: {value}
* Institution Notes: {value}
* Institution Contact Name: {value}
* Institution Contact Email: {value}
* Institution Contact Phone: {value}
* Institution Contact Position: {value}
* Institution Contact Primary: {value}
* Institution Quota: {value}
* Institution Quota Used: {value}
* Institution Quota Period Year: {value}
* Institution Quota Period Term: {value}
* Institution Quota Notes: {value}
* Application Period Year: {value}
* Application Period Term: {value}
* Application Status Application: {value}
* Application Student Access: {value}
* Application Submitted At: {value}
* Application Notes: {value}
* Internship Start Date: {value}
* Internship End Date: {value}
* Internship Status: {value}
* Title: {value}
* Log Date: {value}
* Content: {value}
* Type: {value}

---

## Update – `/monitorings/[id]/update/`
1. Page title: **Update Monitorings**  
2. Inputs:  
   * Internship (Dropdown) (Tom Select) (Disabled)
   * "+" (Button) to add a new Internship
   * Additional Internship (Dropdown) (Tom Select) (appears after pressing +)
   * Log Date (Date)
   * Type (Dropdown) (no Tom Select)
   * Content (Dropdown) (TextArea)
   * "Apply this to all company IDs that match the selected Internship (This will not affect existing ones)" (Checkbox) (Doesn't affect existing ones)
   * Cancel (Button)
   * Save (Button)

3. Notes:
   * All inputs load default values from the database.  
   * Internships are displayed as "{Student Name} - {Institution Name}"; the database still stores IDs' Internship.
   * Additional Internship dropdown only shows Internships from the same institution as the first Internship.  
   * The checkbox automatically adds all Internships from the same institution.
   * Do not display Internships already selected in other inputs.  
   * If all Internships from the same institution are already selected → the + button becomes disabled.  

4. **Cancel** button navigates back.  
5. **Save** button stores changes.  

---

## Delete
Delete records through the **Delete** button in the table at `/monitorings/`.  

---