# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is an IT Asset & Maintenance Management System (IT資產與報修管理系統) project that aims to integrate equipment information, network topology diagrams, account management, maintenance requests, and information security announcements into a comprehensive internal management system.

## Project Structure

Currently this is a specification-only repository containing:
- `spec.md` - Complete project specification in Traditional Chinese

## Project Architecture

The system is designed with the following modules:
- **Asset/Equipment Management** - Device registration with IP, MAC, brand, property numbers, locations
- **Network Topology Management** - Visual network diagrams with image upload and node labeling
- **Account Information Management** - Credentials for VMs, Adobe, GA, Line, etc.
- **VM Server Configuration** - Internal virtual machine management
- **Recurring Payment Management** - Service fees and renewal reminders
- **Tracking Code & GTM Management** - GA, GTM deployment status
- **Maintenance Request Module** - Employee fault reporting and MIS work assignment
- **Information Security Announcement Module** - Security bulletins with read tracking

## Recommended Technology Stack

Based on the specification:
- **Frontend**: Vue 3 + TailwindCSS + Pinia
- **Backend**: Node.js (Express) or PHP
- **Database**: MySQL / SQLite
- **File Upload**: Local uploads/ or S3
- **Authentication**: JWT / Session Based Login

## Development Timeline

The specification suggests a 5-week development timeline:
1. Week 1: Data models and backend structure
2. Week 2: Equipment module and import functionality
3. Week 3: Maintenance module and notification features
4. Week 4: Information bulletin module and tracking analytics
5. Week 5: Integration testing and deployment

## User Roles

- **Administrator**: Full permissions including maintenance assignment and asset editing
- **General User**: Maintenance requests, announcement viewing, equipment queries
- **Signatory**: Read-only access for bulletin acknowledgment

## Key Features to Implement

- Excel import/export functionality
- Image upload for topology diagrams and maintenance photos
- Multi-condition search and filtering
- Email notification system for security announcements
- Read tracking and acknowledgment system
- Maintenance workflow with status tracking