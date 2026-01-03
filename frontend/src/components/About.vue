<template>
    <div class="row">
        <aside class="col-md-3 bg-light p-4">
                <h4>Sidebar</h4>
                <ul class="list-unstyled">
                    <li><a href="#">Info 1</a></li>
                    <li><a href="#">Info 2</a></li>
                    <li><a href="#">Info 3</a></li>
                </ul>
            </aside>

        <div class="col-md-9 p-4">
          <div class="row">
            <h2>About</h2>
            <p>
                This area takes up two-thirds of the page width on medium screens and above. th on medium screens and above. th on medium screens and above.
            </p>
            <pre> 
                Yes. Bootstrap’s grid divides each row into 12 columns.
                Bootstrap Grid System Basics:
                Each row has 12 columns
                col-md-4 = 4 columns (4/12 = 1/3 of the row)
                col-md-8 = 8 columns (8/12 = 2/3 of the row)
                4 + 8 = 12 (fills the entire row)
            </pre>

            <pre>
                Common Column Combinations:
                col-md-6 + col-md-6 = 50/50 split
                col-md-4 + col-md-8 = 33/67 split (your current layout)
                col-md-3 + col-md-9 = 25/75 split
                col-md-12 = full width (100%)
                col-md-2 + col-md-10 = 16.67/83.33 split
            </pre>
            <pre>
                p-4 = "Add padding of size 4 on all sides"

                Bootstrap uses a spacing scale from 0-5, where each number r
                epresents a multiple of 0.25rem (4px by default):
                p-0 = 0 (no padding)
                p-1 = 0.25rem (4px)
                p-2 = 0.5rem (8px)
                p-3 = 1rem (16px)
                p-4 = 1.5rem (24px)
                p-5 = 3rem (48px)
            </pre>
          </div>
        </div>
    </div>
</template>