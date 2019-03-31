<?php

include 'include/header.php';

?>

<html>
    <body>
        <div class = "container">
            <div class = "row">
                <div class = "col-md-2">
                    <h2> Dashboard </h2>
                </div>
                <div class = "col-md-10">
                    <button type="button" class="btn btn-primary float-right">Create New</button>
                </div>
            </div>

            <!-- Filter Options -->
            <div class = "row mt-5">
                <div class = "col-md-6">
                    <div class = "form-inline">
                        <select class = "mr-2 custom-select">
                            <option selected>Delete</option>
                        </select>
                        <button type="button" class="btn btn-primary">Apply</button>

                        <select class = "ml-2 mr-2 custom-select tx">
                            <option selected>Category</option>
                            <option value="1">One</option>
                            <option value="2">Two</option>
                            <option value="3">Three</option>
                        </select>
                        <button type="button" class="btn btn-primary">Filter</button>
                    </div>
                </div>

                <div class = "col-md-6">
                        <div class = "form-inline float-right">
                            <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
                            <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
                        </div>
                </div>
                    
                
            </div>

            <!-- Filter Options -->
            <div class = "row mt-3">
                <div class = "col">
                <table class="table bg-white border border-light shadow-sm mb-5">
                    <thead>
                        <tr>
                        <th></th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Rank</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- one row -->
                        <tr> 
                        <th scope="row">
                             <input type="checkbox" aria-label="Checkbox for following text input">
                        </th>
                        <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>
                        <td>Science</td>
                        <td>01-01-2019</td>
                        <td>1</td>
                        </tr>

                        
                    </tbody>
                </table>
                </div>
            </div>

        </div>


        <!-- Footer -->
        <div class = "footer bg-dark">
            <footer class="navbar navbar-expand-lg navbar-light bg-dark justify-content-between">
                <!-- Content -->
                <a href = "#" class="navbar-brand">
                    <img width="200" class = "img-fluid" src="./images/Logo-black.png">
                </a>

                <!-- Copyright -->
                <div class="text-white nav navbar-nav ml-auto">© 2019 Copyright Michelle & Pirajeev
                </div>
                <!-- Copyright -->

            </footer>
        </div>
        <!-- Footer -->
    </body>
</html>