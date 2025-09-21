# agents/internship.md

The CRUD Internship is used to perform operations on the **internship** table.

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
   * Title: **Filter Internships**  
   * **X** button to close the sidebar  
   * Inputs:  
     * Student Name (Text) (Tom Select)
     * Institution Name (Dropdown) (Tom Select)
     * Period (Dropdown) (Tom Select) (Format: {Period Year} - {Period Term})
     * Start Date (Date) (Display the exact date selected, but if the user inputs an End Date, it will change to a range.)
     * End Date (Date) (Display the exact date selected, but if the user inputs an Start Date, it will change to a range.)
     * Status (Dropdown) (Tom Select)
   * **Reset** button to clear filters  
   * **Apply** button to apply filters  
   * Note: Filters can be combined for more specific searches.  

5. **Table** columns: Student Name, Institution Name, Period Year, Period Term, Start Date, End Date, Status.
   * Anticipate table width exceeding screen size → add horizontal scroll.  
   * Do not force table to stretch; adjust only as needed to fit content.  

6. Show **10 records per page** with **Next** and **Back** navigation.

7. Show the total number of applications.

8. Show page info in the format: `Page X out of N` (X = current page, N = total pages).  

---

## Create – `/internships/create/`
1. Page title: **Create Internship**  
2. Inputs:  
   * Application (Dropdown) (Tom Select)
   * "+" (Button) to add a new Application
   * Additional Application (Dropdown) (Tom Select) (appears after pressing +)
   * Start Date (Date)
   * End Date (Date)
   * Status Application (Dropdown) (no Tom Select)
   * "Apply this to all company IDs that match the selected Application (This will not affect existing ones)" (Checkbox) (Doesn't affect existing ones)
   * Cancel (Button)
   * Save (Button)

3. Notes:
   * Applications are displayed as "{Student Name} - {Institution Name}"; the database still stores IDs' Application.
   * Additional Application dropdown only shows Applications from the same institution as the first Application and make sure that the selected name does not appear again in the dropdown menu.  
   * The checkbox automatically adds all Applications from the same institution.
   * Do not display Applications already selected in other inputs.  
   * If all Applications from the same institution are already selected → the + button becomes disabled.  

4. **Cancel** button navigates back.  
5. **Save** button stores the new data.  

---

## Read – `/internships/[id]/read/`
Internship details displayed as:  
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
* Start Date: {value}
* End Date: {value}
* Status: {value}

---

## Update – `/internship/[id]/update/`
1. Page title: **Update Application**  
2. Inputs:  
   * Application (Dropdown) (Tom Select) (Disabled)
   * "+" (Button) to add a new Application
   * Additional Application (Dropdown) (Tom Select) (appears after pressing +)
   * Start Date (Date)
   * End Date (Date)
   * Status Application (Dropdown) (no Tom Select)
   * "Apply this to all company IDs that match the selected Application (This will not affect existing ones)" (Checkbox) (Doesn't affect existing ones)
   * Cancel (Button)
   * Save (Button)

3. Notes:
   * All inputs load default values from the database.  
   * Applications are displayed as "{Student Name} - {Institution Name}"; the database still stores IDs' Application.
   * Additional Application dropdown only shows Applications from the same institution as the first Application and make sure that the selected name does not appear again in the dropdown menu.
   * The checkbox automatically adds all Applications from the same institution.
   * Do not display Applications already selected in other inputs.  
   * If all Applications from the same institution are already selected → the + button becomes disabled.

4. **Cancel** button navigates back.  
5. **Save** button stores changes.  

---

## Delete
Delete records through the **Delete** button in the table at `/internships/`.  

---