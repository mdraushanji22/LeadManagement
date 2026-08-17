-- Lead Management System Database
-- MySQL Database Schema with Demo Data

CREATE DATABASE IF NOT EXISTS `lead_management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `lead_management`;

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------
CREATE TABLE `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `role` ENUM('admin','user') NOT NULL DEFAULT 'user',
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_email` (`email`),
    KEY `idx_users_role` (`role`),
    KEY `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: leads
-- --------------------------------------------------------
CREATE TABLE `leads` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `customer_name` VARCHAR(150) NOT NULL,
    `company_name` VARCHAR(150) DEFAULT NULL,
    `mobile` VARCHAR(20) NOT NULL,
    `email` VARCHAR(150) DEFAULT NULL,
    `lead_source` VARCHAR(50) NOT NULL,
    `product_service` VARCHAR(255) NOT NULL,
    `priority` ENUM('Low','Medium','High','Urgent') NOT NULL DEFAULT 'Medium',
    `assigned_user_id` INT UNSIGNED DEFAULT NULL,
    `lead_status` ENUM('New','Contacted','Follow Up','Interested','Converted','Not Interested','Lost') NOT NULL DEFAULT 'New',
    `remarks` TEXT DEFAULT NULL,
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_leads_assigned_user` (`assigned_user_id`),
    KEY `idx_leads_created_by` (`created_by`),
    KEY `idx_leads_status` (`lead_status`),
    KEY `idx_leads_source` (`lead_source`),
    KEY `idx_leads_priority` (`priority`),
    KEY `idx_leads_created_at` (`created_at`),
    KEY `idx_leads_email` (`email`),
    KEY `idx_leads_mobile` (`mobile`),
    KEY `idx_leads_deleted_at` (`deleted_at`),
    CONSTRAINT `fk_leads_assigned_user` FOREIGN KEY (`assigned_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_leads_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: follow_ups
-- --------------------------------------------------------
CREATE TABLE `follow_ups` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `lead_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `follow_up_date` DATE NOT NULL,
    `follow_up_type` ENUM('Call','Email','WhatsApp','Meeting') NOT NULL,
    `discussion` TEXT DEFAULT NULL,
    `next_follow_up_date` DATE DEFAULT NULL,
    `status` ENUM('Pending','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_followups_lead` (`lead_id`),
    KEY `idx_followups_user` (`user_id`),
    KEY `idx_followups_date` (`follow_up_date`),
    KEY `idx_followups_status` (`status`),
    CONSTRAINT `fk_followups_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_followups_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: lead_activities
-- --------------------------------------------------------
CREATE TABLE `lead_activities` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `lead_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `activity_type` VARCHAR(50) NOT NULL,
    `description` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_activities_lead` (`lead_id`),
    KEY `idx_activities_user` (`user_id`),
    CONSTRAINT `fk_activities_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_activities_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Demo Data: Users
-- Passwords are hashed using PHP password_hash()
-- Admin password: Admin@123
-- User password: User@123
-- --------------------------------------------------------
INSERT INTO `users` (`name`, `email`, `password`, `phone`, `role`, `status`) VALUES
('Admin User', 'admin@example.com', '$2y$10$G4LhyiSJAA5zNPXWJoaEmeIatlGzNkxZG37.eqt44yBEdyi8aRq5W', '9876543210', 'admin', 'active'),
('Rahul Sharma', 'user@example.com', '$2y$10$L5pp5abQ8H.2q7J/Wyx2IO1/OnDBzxrh.Qb6t4JpJANOM5EtA9Y/a', '9876543211', 'user', 'active'),
('Priya Patel', 'priya@example.com', '$2y$10$L5pp5abQ8H.2q7J/Wyx2IO1/OnDBzxrh.Qb6t4JpJANOM5EtA9Y/a', '9876543212', 'user', 'active'),
('Amit Kumar', 'amit@example.com', '$2y$10$L5pp5abQ8H.2q7J/Wyx2IO1/OnDBzxrh.Qb6t4JpJANOM5EtA9Y/a', '9876543213', 'user', 'active'),
('Neha Singh', 'neha@example.com', '$2y$10$L5pp5abQ8H.2q7J/Wyx2IO1/OnDBzxrh.Qb6t4JpJANOM5EtA9Y/a', '9876543214', 'user', 'inactive');

-- --------------------------------------------------------
-- Demo Data: Leads
-- --------------------------------------------------------
INSERT INTO `leads` (`customer_name`, `company_name`, `mobile`, `email`, `lead_source`, `product_service`, `priority`, `assigned_user_id`, `lead_status`, `remarks`, `created_by`, `created_at`) VALUES
('Rajesh Verma', 'Tech Solutions Pvt Ltd', '9876500001', 'rajesh@techsolutions.com', 'Website', 'CRM Software', 'High', 2, 'Contacted', 'Interested in enterprise plan', 1, '2026-08-01 10:00:00'),
('Sunita Reddy', 'Innovate Design Studio', '9876500002', 'sunita@innovate.com', 'Referral', 'Website Development', 'Medium', 2, 'Follow Up', 'Needs a custom website', 1, '2026-08-02 11:30:00'),
('Manoj Gupta', 'Gupta Trading Co', '9876500003', 'manoj@guptrading.com', 'Google', 'Inventory Software', 'Urgent', 3, 'Interested', 'Looking for immediate solution', 1, '2026-08-03 09:15:00'),
('Kavita Joshi', 'Bright Media Agency', '9876500004', 'kavita@brightmedia.com', 'Facebook', 'Digital Marketing Package', 'Low', 3, 'New', '', 1, '2026-08-04 14:00:00'),
('Arjun Nair', 'Nair Industries', '9876500005', 'arjun@nairind.com', 'LinkedIn', 'ERP System', 'High', 4, 'Converted', 'Deal closed successfully', 1, '2026-08-05 08:45:00'),
('Deepak Mishra', 'Mishra Electronics', '9876500006', 'deepak@mishelec.com', 'Cold Call', 'Point of Sale System', 'Medium', 4, 'Not Interested', 'Already using competitor product', 1, '2026-08-06 16:20:00'),
('Pooja Desai', 'Desai Consultants', '9876500007', 'pooja@desaiconsult.com', 'Instagram', 'HR Management Software', 'Medium', 2, 'Follow Up', 'Scheduled demo for next week', 1, '2026-08-07 10:30:00'),
('Vikram Chauhan', 'Chauhan Exports', '9876500008', 'vikram@chauhanexp.com', 'Advertisement', 'Supply Chain Software', 'High', 3, 'Contacted', 'Sent proposal', 1, '2026-08-08 11:00:00'),
('Anita Kulkarni', 'Kulkarni Associates', '9876500009', 'anita@kulkarniassoc.com', 'Website', 'Accounting Software', 'Low', 4, 'Lost', 'Went with another vendor', 1, '2026-08-09 13:45:00'),
('Sanjay Mehta', 'Mehta Pharma', '9876500010', 'sanjay@mehtapharma.com', 'Referral', 'Inventory Management', 'Urgent', 2, 'Interested', 'Needs urgent implementation', 1, '2026-08-10 09:00:00'),
('Riya Kapoor', 'Kapoor Fashion House', '9876500011', 'riya@kapoorfashion.com', 'Facebook', 'E-commerce Website', 'Medium', 3, 'New', '', 1, '2026-08-11 15:30:00'),
('Ashish Tiwari', 'Tiwari Construction', '9876500012', 'ashish@tiwaricon.com', 'Google', 'Project Management Tool', 'High', 4, 'Contacted', 'Follow up call scheduled', 1, '2026-08-12 10:15:00'),
('Nandini Rao', 'Rao Enterprises', '9876500013', 'nandini@raoenter.com', 'Cold Call', 'CRM Software', 'Medium', 2, 'Follow Up', 'Second follow-up pending', 1, '2026-08-13 11:00:00'),
('Karan Malhotra', 'Malhotra Logistics', '9876500014', 'karan@malhotralog.com', 'LinkedIn', 'Fleet Management Software', 'Urgent', 3, 'Interested', 'Demo completed, awaiting decision', 1, '2026-08-14 09:30:00'),
('Shruti Bhat', 'Bhat Tech Labs', '9876500015', 'shruti@bhattlabs.com', 'Website', 'Cloud Hosting Services', 'Low', 4, 'New', '', 1, '2026-08-15 14:00:00'),
('Rohit Saxena', 'Saxena & Sons', '9876500016', 'rohit@saxenasons.com', 'Advertisement', 'Billing Software', 'Medium', 2, 'Converted', 'Annual license purchased', 1, '2026-08-16 08:00:00'),
('Meena Iyer', 'Iyer Legal Services', '9876500017', 'meena@iyerlegal.com', 'Referral', 'Document Management System', 'High', 3, 'Contacted', 'Sent pricing details', 1, '2026-08-16 10:30:00'),
('Alok Dubey', 'Dubey Food Products', '9876500018', 'alok@dubeyfood.com', 'Instagram', 'Order Management System', 'Medium', 4, 'Follow Up', 'Needs customization', 1, '2026-08-17 09:00:00');

-- --------------------------------------------------------
-- Demo Data: Follow-ups
-- --------------------------------------------------------
INSERT INTO `follow_ups` (`lead_id`, `user_id`, `follow_up_date`, `follow_up_type`, `discussion`, `next_follow_up_date`, `status`, `created_at`) VALUES
(1, 2, '2026-08-05', 'Call', 'Discussed CRM features. Client interested in reporting module.', '2026-08-10', 'Completed', '2026-08-05 10:00:00'),
(1, 2, '2026-08-10', 'Email', 'Sent detailed proposal with pricing.', '2026-08-17', 'Completed', '2026-08-10 11:00:00'),
(1, 2, '2026-08-17', 'Meeting', 'Final meeting to discuss contract terms.', NULL, 'Pending', '2026-08-12 14:00:00'),
(2, 2, '2026-08-07', 'Call', 'Initial discussion about website requirements.', '2026-08-14', 'Completed', '2026-08-07 10:30:00'),
(2, 2, '2026-08-14', 'Meeting', 'Site visit completed. Client wants e-commerce features.', '2026-08-21', 'Pending', '2026-08-14 15:00:00'),
(3, 3, '2026-08-07', 'Call', 'Discussed inventory management needs. Very urgent requirement.', '2026-08-12', 'Completed', '2026-08-07 09:30:00'),
(3, 3, '2026-08-12', 'WhatsApp', 'Shared product brochure and pricing.', '2026-08-19', 'Pending', '2026-08-12 11:00:00'),
(5, 4, '2026-08-08', 'Call', 'Demo completed. Client impressed with ERP features.', '2026-08-12', 'Completed', '2026-08-08 10:00:00'),
(5, 4, '2026-08-12', 'Meeting', 'Contract signed. Implementation starting next week.', NULL, 'Completed', '2026-08-12 14:00:00'),
(7, 2, '2026-08-11', 'Email', 'Sent HR software demo link.', '2026-08-18', 'Pending', '2026-08-11 10:00:00'),
(8, 3, '2026-08-12', 'Call', 'Introduced supply chain solution. Client wants proposal.', '2026-08-19', 'Pending', '2026-08-12 11:30:00'),
(10, 2, '2026-08-14', 'Meeting', 'On-site demo of inventory system. Client very interested.', '2026-08-20', 'Pending', '2026-08-14 10:00:00'),
(13, 2, '2026-08-15', 'Call', 'Second call. Client comparing with competitor.', '2026-08-20', 'Pending', '2026-08-15 11:00:00'),
(14, 3, '2026-08-16', 'Meeting', 'Demo of fleet management software completed.', '2026-08-22', 'Pending', '2026-08-16 10:00:00'),
(16, 2, '2026-08-14', 'Call', 'Final negotiation. Price agreed.', '2026-08-16', 'Completed', '2026-08-14 15:00:00'),
(16, 2, '2026-08-16', 'Meeting', 'Deal closed. Payment received.', NULL, 'Completed', '2026-08-16 11:00:00'),
(17, 3, '2026-08-17', 'Email', 'Sent pricing details for document management system.', '2026-08-22', 'Pending', '2026-08-17 10:30:00'),
(18, 4, '2026-08-17', 'Call', 'Discussed customization requirements for order system.', '2026-08-24', 'Pending', '2026-08-17 09:30:00');

-- --------------------------------------------------------
-- Demo Data: Lead Activities
-- --------------------------------------------------------
INSERT INTO `lead_activities` (`lead_id`, `user_id`, `activity_type`, `description`, `created_at`) VALUES
(1, 1, 'Lead Created', 'Lead created by Admin User.', '2026-08-01 10:00:00'),
(1, 1, 'Lead Assigned', 'Lead assigned to Rahul Sharma.', '2026-08-01 10:01:00'),
(1, 2, 'Status Changed', 'Status changed from New to Contacted.', '2026-08-03 09:00:00'),
(1, 2, 'Follow-up Added', 'Follow-up scheduled for 05 Aug 2026 (Call).', '2026-08-05 10:00:00'),
(1, 2, 'Follow-up Completed', 'Follow-up completed - Discussed CRM features.', '2026-08-05 11:00:00'),
(1, 2, 'Follow-up Added', 'Follow-up scheduled for 10 Aug 2026 (Email).', '2026-08-10 10:00:00'),
(1, 2, 'Follow-up Completed', 'Follow-up completed - Sent detailed proposal.', '2026-08-10 12:00:00'),
(1, 2, 'Status Changed', 'Status changed from Contacted to Follow Up.', '2026-08-10 12:05:00'),
(1, 2, 'Follow-up Added', 'Follow-up scheduled for 17 Aug 2026 (Meeting).', '2026-08-12 14:00:00'),
(2, 1, 'Lead Created', 'Lead created by Admin User.', '2026-08-02 11:30:00'),
(2, 1, 'Lead Assigned', 'Lead assigned to Rahul Sharma.', '2026-08-02 11:31:00'),
(2, 2, 'Follow-up Added', 'Follow-up scheduled for 07 Aug 2026 (Call).', '2026-08-07 10:30:00'),
(2, 2, 'Follow-up Completed', 'Follow-up completed - Initial discussion about website.', '2026-08-07 11:30:00'),
(2, 2, 'Status Changed', 'Status changed from New to Follow Up.', '2026-08-07 11:35:00'),
(3, 1, 'Lead Created', 'Lead created by Admin User.', '2026-08-03 09:15:00'),
(3, 1, 'Lead Assigned', 'Lead assigned to Priya Patel.', '2026-08-03 09:16:00'),
(3, 3, 'Priority Changed', 'Priority changed from Medium to Urgent.', '2026-08-03 09:20:00'),
(3, 3, 'Status Changed', 'Status changed from New to Contacted.', '2026-08-05 10:00:00'),
(3, 3, 'Follow-up Added', 'Follow-up scheduled for 07 Aug 2026 (Call).', '2026-08-07 09:30:00'),
(3, 3, 'Follow-up Completed', 'Follow-up completed - Discussed inventory needs.', '2026-08-07 10:30:00'),
(3, 3, 'Status Changed', 'Status changed from Contacted to Interested.', '2026-08-07 10:35:00'),
(5, 1, 'Lead Created', 'Lead created by Admin User.', '2026-08-05 08:45:00'),
(5, 1, 'Lead Assigned', 'Lead assigned to Amit Kumar.', '2026-08-05 08:46:00'),
(5, 4, 'Status Changed', 'Status changed from New to Contacted.', '2026-08-06 09:00:00'),
(5, 4, 'Follow-up Added', 'Follow-up scheduled for 08 Aug 2026 (Call).', '2026-08-08 10:00:00'),
(5, 4, 'Follow-up Completed', 'Follow-up completed - Demo completed.', '2026-08-08 11:30:00'),
(5, 4, 'Status Changed', 'Status changed from Contacted to Interested.', '2026-08-08 11:35:00'),
(5, 4, 'Status Changed', 'Status changed from Interested to Converted.', '2026-08-12 15:00:00'),
(6, 1, 'Lead Created', 'Lead created by Admin User.', '2026-08-06 16:20:00'),
(6, 1, 'Lead Assigned', 'Lead assigned to Amit Kumar.', '2026-08-06 16:21:00'),
(6, 4, 'Status Changed', 'Status changed from New to Not Interested.', '2026-08-08 10:00:00'),
(6, 4, 'Remark Added', 'Already using competitor product.', '2026-08-08 10:05:00'),
(16, 1, 'Lead Created', 'Lead created by Admin User.', '2026-08-16 08:00:00'),
(16, 1, 'Lead Assigned', 'Lead assigned to Rahul Sharma.', '2026-08-16 08:01:00'),
(16, 2, 'Status Changed', 'Status changed from New to Contacted.', '2026-08-16 09:00:00'),
(16, 2, 'Status Changed', 'Status changed from Contacted to Converted.', '2026-08-16 12:00:00'),
(16, 2, 'Remark Added', 'Annual license purchased.', '2026-08-16 12:05:00');
