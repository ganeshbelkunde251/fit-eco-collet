-- EcoCollect Demo Seed Data
USE `ecocollect`;

-- Seed Waste Categories
INSERT INTO `waste_categories` (`id`, `name`, `description`, `icon`) VALUES
(1, 'Wet Waste', 'Organic kitchen waste, food remnants, vegetable peels, garden leaves, and compostables.', 'bi-trash'),
(2, 'Dry Waste', 'Recyclable packaging, wrappers, clean cardboard, plastic containers, and dry textiles.', 'bi-box-seam'),
(3, 'Plastic', 'Bottles, jugs, bags, caps, synthetic items, and clean single-use plastics.', 'bi-cup-straw'),
(4, 'Paper', 'Newspapers, magazines, office papers, notebooks, cartons, and paper bags.', 'bi-file-earmark-text'),
(5, 'Glass', 'Clear & colored glass bottles, jars, glass food containers, and mirror sheets.', 'bi-gem'),
(6, 'Metal', 'Aluminum cans, tin foil, steel scrap, wire cables, and metal hardware items.', 'bi-hammer'),
(7, 'E-Waste', 'Obsolete electronics, old phones, laptops, chargers, batteries, and circuit boards.', 'bi-cpu'),
(8, 'Hazardous Waste', 'Chemical containers, paint tins, medical waste, batteries, pesticides, and CFL bulbs.', 'bi-exclamation-triangle'),
(9, 'Other', 'Bulky furniture, construction debris, mattresses, and miscellaneous non-standard waste.', 'bi-grid')
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`), `icon` = VALUES(`icon`);

-- Seed Demo Users
-- Admin Passwords: Admin@123 & adminpassword123
-- User Passwords: User@123 & userpassword123
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`) VALUES
(1, 'EcoCollect Admin', 'admin@ecocollect.com', '+91 98230 11223', '$2y$10$BSaHtf1/pKFnYocaN1h/Pez3PH3tF4H09/cbK8eLnoHgVAlAMXfMq', 'admin'),
(2, 'Rahul Sharma', 'rahul@ecocollect.com', '+91 98901 23456', '$2y$10$KQ9gJoYe/.tuTpGkoh4Ze.47Fi9bcRT7zIgeolQJDVI47pRODi1oG', 'user'),
(3, 'Priya Patil', 'priya@ecocollect.com', '+91 97654 32109', '$2y$10$KQ9gJoYe/.tuTpGkoh4Ze.47Fi9bcRT7zIgeolQJDVI47pRODi1oG', 'user'),
(4, 'EcoCollect Admin Local', 'admin@ecocollect.local', '+1 (555) 019-2831', '$2y$10$AJ8xzLXM9wIWSsHnieDSZuBqEAXNTeUCaCbNaImKMREtgOPDgCFnm', 'admin'),
(5, 'Standard User Local', 'user@ecocollect.local', '+1 (555) 014-9922', '$2y$10$Vzb2ubG/Zfyp4g6WAX07C.NggRADm6PiyhfQMJwyOdZiqCjtXCmn.', 'user')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `password` = VALUES(`password`);

-- Seed 16 Realistic Pickup Requests across various statuses and Pune localities
INSERT INTO `pickup_requests` (`request_id`, `user_id`, `waste_category`, `description`, `quantity`, `house_building`, `street_area`, `city`, `state`, `pincode`, `location_notes`, `pickup_date`, `pickup_time`, `status`, `created_at`) VALUES
('EC-2026-00001', 2, 'E-Waste', 'Old Laptop (Dell Inspiron) and broken electronic charger cable', 'Medium', 'Flat 402, Sai Heritage', 'Kothrud', 'Pune', 'Maharashtra', '411038', 'Ring doorbell flat 402', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00 AM', 'Pending', NOW()),
('EC-2026-00002', 2, 'Plastic', '25 empty plastic mineral water bottles and soda containers', 'Small', 'Flat 402, Sai Heritage', 'Kothrud', 'Pune', 'Maharashtra', '411038', 'Keep near entrance door', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '02:00 PM', 'Assigned', NOW()),
('EC-2026-00003', 2, 'Paper', '5 bundles of old newspapers, magazines and corrugated cardboard boxes', 'Large', 'Flat 402, Sai Heritage', 'Kothrud', 'Pune', 'Maharashtra', '411038', 'Call upon arrival', DATE_SUB(CURDATE(), INTERVAL 1 DAY), '11:00 AM', 'Completed', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('EC-2026-00004', 3, 'Glass', '12 clean glass beverage bottles and food storage jars', 'Small', 'Bldg B, Apt 104, Green Acres', 'Baner', 'Pune', 'Maharashtra', '411045', 'Handle with care - fragile glass', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '09:30 AM', 'Scheduled', NOW()),
('EC-2026-00005', 3, 'Hazardous Waste', '2 half-used paint tins, expired motor oil, and 4 lithium rechargeable batteries', 'Medium', 'Bldg B, Apt 104, Green Acres', 'Baner', 'Pune', 'Maharashtra', '411045', 'Stored safely in parking basement', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '03:00 PM', 'Pending', NOW()),
('EC-2026-00006', 2, 'Dry Waste', 'Mixed packaging material, plastic bubble wraps, and clean cardboard packaging', 'Medium', 'Flat 402, Sai Heritage', 'Kothrud', 'Pune', 'Maharashtra', '411038', '', DATE_SUB(CURDATE(), INTERVAL 3 DAY), '10:00 AM', 'Completed', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('EC-2026-00007', 3, 'Wet Waste', 'Organic kitchen waste, vegetable peels, and garden leaves compost material', 'Small', 'Bldg B, Apt 104, Green Acres', 'Baner', 'Pune', 'Maharashtra', '411045', 'Bin placed outside gate', DATE_SUB(CURDATE(), INTERVAL 2 DAY), '09:00 AM', 'Picked Up', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('EC-2026-00008', 2, 'Metal', '15 aluminum beverage cans, tin foils, and steel wire scrap', 'Medium', 'Row House 12, Rosewood Society', 'Wakad', 'Pune', 'Maharashtra', '411057', 'Near main gate', DATE_ADD(CURDATE(), INTERVAL 4 DAY), '11:30 AM', 'Scheduled', NOW()),
('EC-2026-00009', 3, 'E-Waste', 'Old Mobile Phones (Nokia & Samsung), power bank, and headphone cables', 'Small', 'Flat 301, Sunshine Heights', 'Aundh', 'Pune', 'Maharashtra', '411007', 'Contact Priya on arrival', DATE_SUB(CURDATE(), INTERVAL 5 DAY), '02:30 PM', 'Completed', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('EC-2026-00010', 2, 'Paper', 'Office document sheets, notebooks, and shredded paper files', 'Medium', 'Row House 12, Rosewood Society', 'Wakad', 'Pune', 'Maharashtra', '411057', '', DATE_SUB(CURDATE(), INTERVAL 4 DAY), '10:30 AM', 'Completed', DATE_SUB(NOW(), INTERVAL 4 DAY)),
('EC-2026-00011', 3, 'Other', 'Old broken wooden chair and discarded mattress padding', 'Large', 'Flat 301, Sunshine Heights', 'Aundh', 'Pune', 'Maharashtra', '411007', 'Heavy item - requires 2 collectors', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '04:00 PM', 'Assigned', NOW()),
('EC-2026-00012', 2, 'Plastic', 'Bulk plastic crates, synthetic packaging film, and plastic containers', 'Large', 'Plot 55, Industrial Zone', 'Hadapsar', 'Pune', 'Maharashtra', '411028', 'Commercial site gate #2', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00 AM', 'Pending', NOW()),
('EC-2026-00013', 3, 'Hazardous Waste', 'Used lead batteries, fluorescent CFL light bulbs, and chemical sprays', 'Medium', 'Flat 12A, Orchid Towers', 'Viman Nagar', 'Pune', 'Maharashtra', '411014', 'Toxic safety gloves needed', DATE_SUB(CURDATE(), INTERVAL 6 DAY), '11:00 AM', 'Completed', DATE_SUB(NOW(), INTERVAL 6 DAY)),
('EC-2026-00014', 2, 'Glass', 'Broken window glass pane wrapped in heavy cardboard box', 'Medium', 'Row House 12, Rosewood Society', 'Wakad', 'Pune', 'Maharashtra', '411057', 'Marked Glass - Caution', DATE_SUB(CURDATE(), INTERVAL 1 DAY), '03:30 PM', 'Cancelled', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('EC-2026-00015', 3, 'E-Waste', 'Old CRT Monitor, desktop keyboard, mouse, and internal power supply unit', 'Medium', 'Flat 12A, Orchid Towers', 'Viman Nagar', 'Pune', 'Maharashtra', '411014', 'Elevator available', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '10:00 AM', 'Scheduled', NOW()),
('EC-2026-00016', 2, 'Wet Waste', 'Decomposed organic garden leaves and compost waste', 'Small', 'Plot 55, Industrial Zone', 'Hadapsar', 'Pune', 'Maharashtra', '411028', 'Compost pit side', DATE_SUB(CURDATE(), INTERVAL 2 DAY), '08:30 AM', 'Picked Up', DATE_SUB(NOW(), INTERVAL 2 DAY))
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`);
