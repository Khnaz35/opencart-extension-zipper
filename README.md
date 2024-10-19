# OpenCart Extension Packaging Tool

## Overview

The **OpenCart Extension Packaging Tool** is a simple program designed to streamline the development and packaging process for OpenCart extensions. This tool allows developers to specify new files or folders created during development, simplifying the task of organizing and zipping files for installation on another OpenCart instance.

## Features

- **File and Folder Management**: Easily add new files or folders for your OpenCart extension.
- **Automatic Zipping**: Click the package button to organize and zip files for installation, eliminating the need for manual copying and zipping.
- **VS Code Integration**: Supports developers using **VS Code** for managing OpenCart projects. Right-click to copy file paths and paste them directly into the tool.
- **Base Path Adjustment**: Allows adjustment of the base path to ensure files are correctly structured for OpenCart.

## Installation

1. Download the source code from the repository.
2. Extract the files to a local directory.
3. Open the tool in your preferred development environment.

## Usage

1. **Adding Files/Folders**: 
   - Enter the file or folder path in the input field. 
   - Adjust the base path as necessary to match your OpenCart structure.
   - Click **Add** to include the specified file or folder in the package list.

2. **Packaging**:
   - Once you have added all necessary files and folders, click the **Package** button.
   - The tool will organize the files and create a compressed zip file of the extension.
   - The zip file can be installed on another OpenCart instance for testing.

## Requirements

- **PHP**: Ensure you have PHP installed on your system.
- **VS Code**: Recommended for managing your OpenCart development projects.

## Contribution

Contributions to improve this tool are welcome! If you have suggestions or enhancements, please submit a pull request or open an issue on the GitHub repository.
