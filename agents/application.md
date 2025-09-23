# agents/application.md

The CRUD Application is used to perform operations on the **application** table.

> Before reading this document, make sure you have read **AGENTS.md** to understand the context.

---

## Access Rights
* **Create**: The Student role can only create an application for themselves if and only if he does not have other application data. Higher roles can perform full Create operations.
* **Read**: The Student role can only view their own application. Higher roles can perform full Read operations.  
* **Update**: The Student role can only update if the *Student Access* column is set to **True**. Higher roles can perform full Update operations.  
* **Delete**: The Student role can only delete if the *Student Access* column is set to **True**.. Higher roles can perform full Delete operations.  

---

## List – `/applications/`
1. Page title: **Applications**

2. **Search Input**  
   * Search across all displayed table columns (no 10-record limit).  
   * Search runs automatically whenever the input changes.  
   * A **Search** button is provided in case auto-search does not work.

3. **Button filter**
   * This button only appears when applying filters.
   * The number of buttons corresponds to how many filters are applied.
   * Button format: “{filter name}: {filter value}”

4. **Filter** (sidebar opens from the right when the filter button is clicked)  
   * Title: **Filter Applications**  
   * **X** button to close the sidebar  
   * Inputs:  
     * Student Name (Text)  
     * Institution Name (Text)  
     * Period (Dropdown) (Tom Select) (format: "{year}: {term}")
     * Status Application (Dropdown, no Tom Select)  
     * Student Access (Radio: True / False / Any)  
     * Submitted At (Date)  
     * Have Notes? (Radio: True / False / Any)  
   * **Reset** button to clear filters  
   * **Apply** button to apply filters  
   * Note: Filters can be combined for more specific searches.  

5. **Table** columns: Student Name, Institution Name, Year, Term, Status Application, Student Access, Submitted At.  
   * Anticipate table width exceeding screen size → add horizontal scroll.  
   * Do not force table to stretch; adjust only as needed to fit content.  

6. Show **10 records per page** with **Next** and **Back** navigation.

7. Show the total number of applications.

8. Show page info in the format: `Page X out of N` (X = current page, N = total pages).  

---

## Create – `/applications/create/`
1. Page title: **Create Application**  
2. Inputs:  
   * Student Name (Dropdown, Tom Select)
   * "+" (Button) to add a new Student Name
   * Additional Student Name (Dropdown, Tom Select, appears after pressing +)
   * Apply to all students who do not yet have the application (Checkbox)
   * Institution Name (Dropdown, Tom Select)  
   * Period (Dropdown) (Tom Select) (format: "{year}: {term}")
   * Status Application (Dropdown, no Tom Select)
   * Student Access (Radio: True / False / Any) (Display the input only if the role is not a student)
   * Submitted At (Date)
   * Notes (Textarea)
   * Cancel (Button)
   * Save (Button)

3. Notes:  
   * Student Name input shows the name, not the ID. Database still stores the ID.  
   * Period shows the year and term, not the ID, Database still stores the ID.
   * Additional Student Name dropdown, make sure that the selected name does not appear again in the dropdown menu.
   * Institution Name works the same way.

4. **Cancel** button navigates back.  
5. **Save** button stores the new data.  

---

## Read – `/applications/[id]/read/`
Application details displayed as:  
* Student Photo
* Student Name (click → `/students/[id]/read/`)  
* Student Email
* Student Phone  
* Student Number  
* National Student Number  
* Student Major  
* Student Class  
* Student Batch  
* Student Notes  
* Institution Photo  
* Institution Name (click → `/institutions/[id]/read/`)  
* Institution Address  
* Institution City  
* Institution Province  
* Institution Website  
* Institution Industry  
* Institution Notes  
* Institution Contact Name  
* Institution Contact Email  
* Institution Contact Phone  
* Institution Contact Position  
* Institution Contact Primary  
* Institution Quota  
* Institution Quota Used  
* Institution Quota Period Year  
* Institution Quota Period Term  
* Institution Quota Notes  
* Period Year  
* Period Term  
* Status Application  
* Student Access  
* Submitted At  
* Notes  

---

## Update – `/applications/[id]/update/`
1. Page title: **Update Application**  
2. Inputs:  
   * Student Name (Dropdown, Tom Select – disabled, default from database)
   * "+" (Button) to add a new Student Name
   * Additional Student Name (Dropdown, Tom Select, appears after pressing +)
   * Apply to all applications with the same institution (Checkbox)
   * Institution Name (Dropdown, Tom Select)
   * Period (Dropdown) (Tom Select) (format: "{year}: {term}")
   * Status Application (Dropdown, no Tom Select)
   * Student Access (Radio: True / False / Any) (Display the input only if the role is not a student)
   * Submitted At (Date)
   * Notes (Textarea)
   * Cancel (Button)
   * Save (Button)

3. Notes:  
   * Student Name & Institution Name are displayed as names; the database still stores IDs.
   * All inputs load default values from the database.
   * Additional Student Name dropdown only shows students from the same institution as the first Student Name and make sure that the selected name does not appear again in the dropdown menu.
   * The checkbox automatically adds all Student Names from the same institution.
   * Do not display Student Names already selected in other inputs.
   * If all Student Names from the institution are already selected → the + button becomes disabled.
   * Period shows the year and term, not the ID, Database still stores the ID.

4. **Cancel** button navigates back.  
5. **Save** button stores changes.  

---

## Delete
Delete records through the **Delete** button in the table at `/applications/`.  

---