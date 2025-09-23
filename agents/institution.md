# agents/institution.md

CRUD Institution is used to perform operations on the **institution** table, **institution_contact**, and **institution_quotas**.

> Before reading this document, make sure you have already read **AGENTS.md** to understand the context.

---

## Access Rights
* **Create**: Only the student role cannot do this.
* **Read**: Only students and the students themselves can view their own internship institution data, provided that the students have the application data; Roles above Student can perform full Read operations.
* **Update**: Only the student role cannot do this.
* **Delete**: Only the student role cannot do this.

---

## List – `/institutions/`

1. Page title: **Institutions**.  

2. **Button filter**
   * This button only appears when applying filters.
   * The number of buttons corresponds to how many filters are applied.
   * Button format: “{filter name}: {filter value}”

3. **Search Input**  
   * Search records based on all columns displayed in the table (no 10-record limit).  
   * Search runs automatically whenever the input changes.  
   * The **Search** button is provided in case the automatic search does not work.

3. **Filter** (sidebar opens from the right after clicking the filter button):  
   * Title: **Filter Inbstitutions**  
   * **X** button to close the sidebar  
   * Inputs:  
     * Name (text)
     * Address (textArea)
     * City (Dropdown) (Tom Select) 
     * Province (Dropdown) (Tom Select)
     * Website (Text)
     * Industry (Dropdown) (Tom Select)
     * Have Notes? (radio: True / False / Any)  
     * Have Photo? (radio: True / False / Any)  
     * Contact Name (text)
     * Contact E-Mail (email)  
     * Contact Phone (number)  
     * Contact Position (text) 
     * Contact Is Primary? (radio: True / False / Any)
     * Period Year (date) (Year only)  
     * Period Term (number)  
     * Quota (number)  
     * Quota Used (number)  
   * **Reset** button to clear filters  
   * **Apply** button to apply filters  
   * Note: Filters can be combined for more specific search results.  

4. **Table** with columns: Name, City, Province, Industry, Contact Name, Contact E-Mail, Contact Phone, Contact Position, Period Year, Period Term, Quota, Quota Used.

Notes: Anticipate if the table width exceeds the screen width due to its content. By adding a horizontal scroll bar below the table if it exceeds the screen width. Don't force the table to be long and wide explicitly, but adjust it to the content.

5. Display **10 records per page**, with **Next** and **Back** navigation.  

6. Display the total number of Institutions.  

7. Display page information in the format: `Page X out of N` (X = current page, N = total pages).  

---

## Create – `/institutions/create/`

1. Page title: **Create Institution**.  

2. Inputs:  
     * Name (text)
     * Address (text)
     * City (Dropdown) (Tom Select) 
     * Province (Dropdown) (Tom Select)
     * Website (Text)
     * Industry (Dropdown) (Tom Select)
     * Notes (TextArea)  
     * Photo (text)  
     * Contact Name (text)
     * Contact E-Mail (email)  
     * Contact Phone (number)  
     * Contact Position (text) 
     * Contact Is Primary? (radio: True / False / Any)
     * Period (Dropdown) (Tom Select) (format: "{year}: {term}") with a **Create new period** button beside it. Clicking the button hides the dropdown and reveals the inputs below.
     * New Period Year (number) (Displayed only after pressing **Create new period**)
     * New Period Term (number) (Displayed only after pressing **Create new period**)
     * Quota (number)

3. Notes:  
   * ID is not an input field.  
   * Institution_ID is not an input field.
   * If the Year + Term Period combination does not yet exist in the Period table → create a new record, then link with its ID. If it already exists → just link to the existing ID.  
   * Used is assigned automatically.  

4. **Cancel** button to go back.  

5. **Save** button to store the new data.  

---

## Read – `/institutions/[id]/read/`

Institution details are displayed as:  
* Photo: {value}  
* Name: {value}  
* Address: {value}  
* City: {value}  
* Province: {value}
* Website: {value}  
* Industry: {value}  
* Notes: {value}  
* Contact Name: {value}  
* Contact E-Mail: {value}  
* Contact Phone: {value}
* Contact Position: {value}
* Contact Is Primary? {value}
* Period Year {value}
* Period Term {value}  
* Quota {value}
* Used {value}
---

## Update – `/institutions/[id]/update/`

1. Page title: **Update Institution**.  

2. Inputs:  
     * Name (text) (Disabled)
     * Address (text)
     * City (Dropdown) (Tom Select) 
     * Province (Dropdown) (Tom Select)
     * Website (Text)
     * Industry (Dropdown) (Tom Select)
     * Notes (TextArea)  
     * Photo (text)  
     * Contact Name (text)
     * Contact E-Mail (email)  
     * Contact Phone (number)  
     * Contact Position (text) 
     * Contact Is Primary? (radio: True / False / Any)
     * Period (Dropdown) (Tom Select) (format: "{year}: {term}") with a **Create new period** button beside it. Clicking the button hides the dropdown and reveals the inputs below.
     * New Period Year (number) (Displayed only after pressing **Create new period**)
     * New Period Term (number) (Displayed only after pressing **Create new period**)
     * Quota (number)

3. Notes:
   * All inputs have default values from the database, except Password.  
   * ID is not an input field.  
   * Institution_ID is not an input field.
   * If the Year + Term Period combination does not yet exist in the Period table → create a new record, then link with its ID. If it already exists → just link to the existing ID.
   * Used is assigned automatically.  

4. **Cancel** button to go back.  

5. **Save** button to store the changes.  

---

## Delete

Delete records using the **Delete** button in the table at the `/institutions/` endpoint.  

---
