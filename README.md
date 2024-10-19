# Extension Packager for OpenCart Developers

## Overview
This tool is designed for OpenCart developers to streamline the process of organizing and packaging files and folders created during the development of extensions or modules. Instead of manually copying files and creating a ZIP archive each time, this tool automates the process, making it easier and faster to prepare your extension for testing or deployment.

## Features
- **File/Folder Management**: Add or remove file and folder paths related to your OpenCart extension development.
- **VS Code Integration**: Supports developers using **VS Code** for managing OpenCart projects. Right-click to copy file paths and paste them directly into the tool.
- **Organize Files**: Automatically structure and organize your extension files based on OpenCart standards.
- **Package Creation**: Generate a ZIP file containing all the necessary files and folders for easy installation on another OpenCart instance.
- **Path Persistence**: Save file paths for your extension and retrieve them anytime during development.
- **Clear Paths**: Option to clear saved file paths for re-organization or updates.

## How It Works
1. **Add Files or Folders**: Specify the files or folders that you’ve created or modified for your OpenCart extension.
2. **Save Paths**: Save the file paths to the database using the form provided.
3. **Package Extension**: Click the "Package" button to organize and bundle all files into a ZIP archive automatically. This ZIP file can then be used to install the extension on another OpenCart instance for testing or deployment.
4. **Clear Paths**: If needed, you can clear the previously saved paths to start fresh.

## How to Use
1. **Adding Files/Folders**:
   - Input the extension name and OpenCart version in the provided form.
   - Add the paths of the files or folders you've created or modified during development.
   - Save the paths by clicking the "Save Paths" button.

2. **Packaging the Extension**:
   - After completing the development, click the "Package" button.
   - The tool will automatically create a ZIP archive with the required folder structure (`upload` folder inside the ZIP) and include all specified files and folders.

3. **Clearing Paths**:
   - If you need to modify or reset the paths, use the "Clear Paths" button to remove the saved paths from the database for the current extension version.

## Requirements
- **PHP**: Ensure that your server has PHP installed and configured.
- **MySQL**: The tool uses a MySQL database to store file paths and extension data.
- **OpenCart**: Developed specifically for OpenCart extension developers.
- **VS Code**: Recommended for managing your OpenCart development projects.

## Installation & Setup
1. Clone or download the repository.
2. Modify the database connection details in the PHP code:
   ```php
   $host = 'localhost'; // Your database host
   $db = 'extension_packager'; // Your database name
   $user = 'root'; // Your database username
   $pass = ''; // Your database password
   ```
3. Set up your MySQL database using the following table schema:
   ```sql
   CREATE TABLE file_paths (
       id INT AUTO_INCREMENT PRIMARY KEY,
       extension_name VARCHAR(255),
       opencart_version VARCHAR(10),
       file_path TEXT,
       zip_name VARCHAR(255)
   );
   ```
4. Adjust the base path as necessary to match your OpenCart structure.
5. Open the tool in your browser, specify your extension files and folders, and start packaging your extensions.

## Contribution

Contributions to improve this tool are welcome! If you have suggestions or enhancements, please submit a pull request or open an issue on the GitHub repository.
