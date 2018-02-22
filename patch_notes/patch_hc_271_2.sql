-- phpMyAdmin SQL Dump
-- version 4.3.7
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Сен 15 2016 г., 16:48
-- Версия сервера: 5.6.21-log
-- Версия PHP: 5.4.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `test`
--

-- --------------------------------------------------------

--
-- Структура таблицы `regulations`
--
DROP TABLE IF EXISTS `regulations`;

CREATE TABLE IF NOT EXISTS `regulations` (
  `rg_id` int(11) NOT NULL,
  `rg_pp_name` varchar(255) NOT NULL,
  `rg_pp_number` varchar(255) NOT NULL,
  `rg_number` varchar(4096) NOT NULL,
  `rg_description` text NOT NULL,
  `rg_rgc_id` int(111) DEFAULT NULL,
  `rg_u_owner_id` int(11) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=114 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `regulations`
--

INSERT INTO `regulations` (`rg_id`, `rg_pp_name`, `rg_pp_number`, `rg_number`, `rg_description`, `rg_rgc_id`, `rg_u_owner_id`) VALUES
(1, 'Notice of Privacy Practices', 'PR-105', '45 C.F.R. §164.520', '', 5, NULL),
(2, 'Confidentiality/Privacy of PHI', 'AS-105', '45 C.F.R. §164.308(a)(4)(ii)(B)<br>45 C.F.R. §164.502(a)<br>45 C.F.R. §164.502(b)<br>45 C.F.R. §164.514', '', 1, NULL),
(3, 'Pledge of Confidentiality', 'PR-110', '45 C.F.R. §164.502(a)', '', 5, NULL),
(4, 'Use of PHI', 'PR-103', '§164.502(a);§164.502(b);§164.514', '', 5, NULL),
(5, 'Minimum Necessary Use & Disclosure of PHI/ePHI', 'AS-110', '45 CFR §164.308(a)(3)(ii)(A) and (B)<br>45 CFR §164.308(a)(4)(ii)(B)<br>45 CFR §164.502(b)<br>45 CFR §164.514(d)', '', 1, NULL),
(6, 'Policy for Requiring that Plan Documents include HIPAA disclosure constraints', 'AS-115', '45 C.F.R. §164.308 <br>45 C.F.R. §164.314(a)<br>45 C.F.R. §164.502(e)<br>45 C.F.R. §164.504(e)(1)', '', 1, NULL),
(7, 'Acknowledgement of Receipt of Notice of Privacy Practices', 'PR-120', '45 C.F.R. § 164.520', '', 5, NULL),
(8, 'Implementation Specifications', 'AS-120', '45 C.F.R. §164.306(d)(1)<br>45 C.F.R. §164.306(d)(2)', '', 1, NULL),
(9, 'Development and Maintenance of Privacy Policies and Procedures', 'AS-125', '45 CFR §164.316(a)', '', 1, NULL),
(10, 'Disciplinary Actions for Breach of Confidentiality/ Privacy or Security- Sanctions & Penalties', 'AS-130', '45 CFR §164.308(a)(1)(ii)(C)', '', 1, NULL),
(11, 'Education and Training', 'AS-270', '45 C.F.R. §164.308(a)(5)(ii)(A)<br>45 C.F.R. §164.502(a)<br>45 C.F.R. §164.502(b)<br>45 C.F.R. §164.514<br>45 C.F.R. §164.530', '', 1, NULL),
(12, 'Security Reminders', 'AS-135', '45 CFR § 164.308(a)(5)(ii)(A)', '', 1, NULL),
(13, 'Definition of Terms', 'AS-140', '§164.502(a)', '', 1, NULL),
(14, 'Job Description, Chief Privacy Officer', 'AS-140', '45 CFR § 164.503(a)(2)<br>45 CFR § 164.530', '', 1, NULL),
(15, 'Job Description – Chief Security Officer', 'AS-145', '45 CFR §164.308(a)(2)(i)', '', 1, NULL),
(16, 'Non-Retaliation Policy', 'AS-150', '45 C.F.R. §164.530(g)', '', 1, NULL),
(17, 'Disposal of ePHI and/or Hardware', 'PS-105', '45 C.F.R §164.310(d)(2)(i)', '', 2, NULL),
(18, 'E-Mail Policy', 'PS-110', '', '', 2, NULL),
(19, 'Fax Transmittal of PHI', 'AS-155', '45 C.F.R.§164.530(c)', '', 1, NULL),
(20, 'Removal/Transporting PHI', 'AS-165', '45 C.F.R.§164.530(c)', '', 1, NULL),
(21, 'Reporting of Privacy Concern and Security Breach Policy', 'AS-170', '45 CFR § 164.308(a)(6)(ii)<br>45 CFR § 164.530(d)', '', 1, NULL),
(22, 'What Constitutes a Breach of PHI', 'AS-180', '45 CFR § 164.404', '', 1, NULL),
(23, 'Tracking Privacy & Security Breach Disclosures', 'AS-185', '45 CFR §164.308(a)(1)(ii)(d)<br>45 CFR §164.400', '', 1, NULL),
(24, 'Mitigation After Improper Use and Disclosure of PHI', 'AS-190', '45 C.F.R. §164.530(f)', '', 1, NULL),
(25, 'HIPAA Fraud and Abuse', 'AS-195', '', '', 1, NULL),
(26, 'Access & Denial of Request for PHI', 'PR-130', '45 C.F.R. §164.524', '', 5, NULL),
(27, 'Amending PHI', 'PR-135', '45 C.F.R. §164.526', '', 5, NULL),
(28, 'Accounting of Disclosures', 'PR-140', '45 C.F.R. §164.528', '', 5, NULL),
(29, 'Restricting Use of PHI', 'AS-200', '45 C.F.R.§164.308(a)(3)(ii)(A)<br>45 C.F.R.§164.522', '', 1, NULL),
(30, 'Patient''s Request for Communications via Alternative Forms – part of Restricting Use and Disclosure', 'PR-145', '45 C.F.R. §164.522(b)(1)', '', 5, NULL),
(31, 'Breach Notification Policy and Procedures', 'PR-150', '45 C.F.R. §164.400', '', 5, NULL),
(32, 'Patient Authorization', 'PR-155', '45 CFR §164.312(c)(2)', '', 5, NULL),
(33, 'Family, Friends Notification', 'PR-160', '45 C.F.R. §164.510(b)', '', 5, NULL),
(34, 'Fundraising', 'PR-165', '45 C.F.R. §164.514(f)(1)', '', 5, NULL),
(35, 'Marketing', 'PR-170', '45 C.F.R. §164.508(a)(3)', '', 5, NULL),
(36, 'Minors and Next of Kin', 'PR-175', '45 C.F.R. §164.502(g)', '', 5, NULL),
(37, 'Research', 'PR-180', '45 C.F.R. §164.512(i)(1)<br>45 C.F.R. §164.512(i)(2)', '', 5, NULL),
(38, 'Revocation of Authorization', 'PR-185', '45 C.F.R. §164.508(b)(2)(iii)<br>45 C.F.R. §164.508(c)(2)(i)<br>45 C.F.R. §164.514(f)(1)<br>45 C.F.R. §164.520(b)(1)(ii)', '', 5, NULL),
(39, 'Isolating Healthcare Clearinghouse Functions', 'AS-205', '45 C.F.R §164.308(a)(4)(ii)(A)', '', 1, NULL),
(40, 'Use and Disclosure, Judicial or Administrative', 'PR-190', '45 C.F.R. §164.512(e )', '', 5, NULL),
(41, 'De-Identified Patient Information', 'PR-250', '45 C.F.R. §164.514(b)', '', 5, NULL),
(42, 'Development and Maintenance of Security Policies and Procedures', 'DR-105', '45 C.F.R §164.316(a)', '', 4, NULL),
(43, 'Risk Analysis', 'AS-210', '45 CFR §164.308(a)(1)(ii)(A)<br>45 CFR §164.308(a)(1)(ii)(B)', '', 1, NULL),
(44, 'Protection from Malicious Software', 'AS-215', '45 CFR § 164.308(a)(5)(ii)(B)', '', 1, NULL),
(45, 'Log in Monitoring', 'AS-220', '45 CFR § 164.308(a)(1)(ii)(D)<br>45 CFR § 164.308(a)(5)(ii)(C)', '', 1, NULL),
(46, 'Password Management', 'TS-105', '45 C.F.R.§ 164.308(a)(5)(ii)(D)<br>45 C.F.R.§ 164.312(a)(2)(i)', '', 3, NULL),
(47, 'Data Back-up and Storage', 'AS-225', '45 CFR § 164.308(a)(7)(ii)(A)', '', 1, NULL),
(48, 'Receipt and Removal of Hardware Containing ePHI', 'PS-115', '45 C.F.R §164.310(d)(2)(i)', '', 2, NULL),
(49, 'Automatic Logoff', 'TS-110', '45 CFR § 164.312(a)(2)(iii)', '', 3, NULL),
(50, 'Encryption and Decryption of Electronically Transmitted Data', 'TS-115', '45 CFR § 164.312(a)(2)(iv)<br>45 CFR § 164.312(e)(2)(ii)', '', 3, NULL),
(51, 'Facility Access Controls', 'PS-120', '45 C.F.R.§ 164.310(a)(2)(ii)', '', 2, NULL),
(52, 'Access Controls and Validation Procedures', 'PS-125', '45 C.F.R §164.310(a)(2)(iii)', '', 2, NULL),
(53, 'Facility Security Plan', 'PS-130', '45 CFR § 164.310(a)(2)(ii)', '', 2, NULL),
(54, 'Periodic Evaluation of Privacy and Security Policies Procedures', 'DR-110', '45 C.F.R. §164.316(b)(2)(iii)', '', 4, NULL),
(55, 'Standard Documentation Review and Retention', 'DR-115', '45 C.F.R §164.316 (b)(2)(ii)<br>45 C.F.R.§164.530(j)', '', 4, NULL),
(56, 'Employee Use of Social Media', 'PR-255', '45 C.F.R. §164.502(a)', '', 5, NULL),
(57, 'Use of Mobile Devices', 'PR-255', 'Privacy and Security', '', 5, NULL),
(58, 'Workstation Use', 'PS-135', '45 CFR §164.310(b)', '', 2, NULL),
(59, 'Disaster Recovery Plan', 'AS-230', '45 CFR §164.308(a)(7)(ii)(B)', '', 1, NULL),
(60, 'Emergency-mode Operation Plan', 'AS-235', '45 C.F.R §164.308(a)(7)(ii)(C)', '', 1, NULL),
(61, 'Access Control and Validation', 'PS-140', '45 CFR §164.310(a)(2)(iii)', '', 2, NULL),
(62, 'Workstation Security', 'PS-145', '45 C.F.R §164.310(c)', '', 2, NULL),
(63, 'Media Reuse', 'PS-150', '45 CFR §164.310(d)(2)(ii)', '', 2, NULL),
(64, 'Testing and Revision Procedures – Contingency Plans', 'AS-240', '45 CFR §164.308(a)(7)(ii)(D)', '', 1, NULL),
(65, 'Contingency Operations', 'PS-155', '45 CFR §164.310(a)(2)(i)', '', 2, NULL),
(66, 'Maintenance Records', 'PS-160', '45 CFR §164.310(a)(2)(iv)', '', 2, NULL),
(67, 'Accountability for Movement of Equipment and Media', 'PS-165', '45 CFR §164.310(d)(2)(iii)', '', 2, NULL),
(68, 'Availability of Documented Policies and Procedures', 'DR-120', '45 C.F.R §164.316 (b)(2)(ii)', '', 4, NULL),
(69, 'Integrity Controls – Data Transmission', 'TS-120', '45 CFR §164.312(e)(2)(i)', '', 3, NULL),
(70, 'Workforce Clearance Procedure', 'AS-245', '§164.308(a)(3)(ii)(B);§164.308(a)(3)(ii)(C)', '', 1, NULL),
(71, 'Integrity - Protect ePHI from improper alteration or destruction', 'TS-125', '45 CFR §164.312(c)(1)', '', 3, NULL),
(72, 'Audit Controls', 'TS-130', '45 CFR §164.312(b)', '', 3, NULL),
(73, 'Applications and Data Criticality Analysis', 'AS-250', '45 CFR §164.308(a)(7)(ii)(E)', '', 1, NULL),
(74, 'Data Backup and Storage', 'TS-135', '45 C.F.R §164.310(d)(2)(iv)', '', 3, NULL),
(75, 'Emergency Access Procedure', 'TS-140', '45 C.F.R §164.312(a)(2)(ii)', '', 3, NULL),
(76, 'Person or Entity Authentication', 'TS-145', '45 CFR §164.312(a)(2)(i)<br>45 CFR §164.312(d)', '', 3, NULL),
(77, 'Device and Media Controls – Accountability', 'AS-255', '45 C.F.R §164.310(d)(2)(iii)', '', 1, NULL),
(78, 'Consent for Treatment, Payment and Healthcare Operations (Policy)', 'PR-265', '45 C.F.R. §164.512(e)<br>45 C.F.R. §164.530(h)', '', 5, NULL),
(79, 'Policies and Procedures for Conducting Business with Business Associate', 'AS-260', '45 CFR	§164.314(a)(2)(i)(C)<br>45 CFR §164.308<br>45 CFR §164.308(a)(8)(4)<br>45 CFR §164.314(a)(1)(ii)<br>45 CFR §164.314(a)(2)(i)(A)<br>45 CFR §164.314(a)(2)(i)(B)<br>45 CFR §164.314(a)(2)(i)(D)<br>45 CFR §164.314(a)(i)<br>45 CFR §164.314(a)(ii)<br>45 CFR §164.502(e)<br>45 CFR §164.504(e)(1)', '', 1, NULL),
(80, 'Policies and procedures for identifying business associates and distributing BA amendments', 'AS-265', '45 CFR §164.314(a)(i)(2)', '', 1, NULL),
(81, 'Monitoring of PHI Disclosures by Business Associates', 'PR-270', '45 CFR § 164.410', '', 5, NULL),
(82, 'test P&P name', 'test-P&P-number', 'test-Reg-number', 'test Description', NULL, 63),
(83, 'test P&P name t1', 'test-P&P-number t1', 'test-Reg-number t1', 'test Description t1', NULL, 167),
(84, 'tets', 'test1', 'test2', 'test3', NULL, 63),
(85, 'qq', 'qq', 'qq', 'qq', NULL, 170),
(86, 'qq', 'qq', 'qq', 'qq', NULL, 170),
(87, 'Formulating the HIPAA Compliance Plan', 'AS-100', '45 C.F.R. §164.306(d)(1)<br>45 C.F.R. §164.306(d)(2)', '', 1, NULL),
(88, 'Asset Inventory', 'AS-122', '45 C.F.R. §164.308(a)(1)(i)', '', 1, NULL),
(89, 'Termination Procedure', 'AS-132', '45 C.F.R. §164.308(a)(3)(ii)(C)', '', 1, NULL),
(90, 'Workforce Clearance Procedures', 'AS-134', '45 CFR 164.308(a)(3)(ii)(B)', '', 1, NULL),
(91, 'Incidental Use and Disclosure of Protected Health Information', 'AS-182', '45 CFR §164.502 (a)<br>45 CFR §164.502 (b)<br>45 CFR §164.514 (d)<br>45 CFR §164.530 (c)(2)', '', 1, NULL),
(92, 'Restriction Form', 'AS-200a', '§164.308(a)(3)(ii)(A) §164.308(a)(3)(ii)(B) §164.522', '', 1, NULL),
(93, 'Requests for Restricting Use and Disclosure of PHI – Log', 'AS-200b', '§164.308(a)(3)(ii)(A) §164.308(a)(3)(ii)(B) §164.522', '', 1, NULL),
(94, 'Circuit Diagram', 'AS-255b', '45 CFR §164.310(d)(2)(iii)', '', 1, NULL),
(95, 'Business Associate Due Diligence', 'AS-261', '45 C.F.R. §164.308(a)(5)(ii)(A)<br>45 C.F.R. §164.502(a)<br>45 C.F.R. §164.502(b)<br>45 C.F.R. §164.514<br>45 C.F.R. §164.530', '', 1, NULL),
(96, 'Use of PHI', 'PR-115', '45 C.F.R. § 164.501<br>45 C.F.R. § 164.502<br>45 C.F.R. § 164.504<br>45 C.F.R. § 164.506<br>45 C.F.R. § 164.506(a)<br>45 C.F.R. § 164.508<br>45 C.F.R. § 164.508(3)<br>45 C.F.R. § 164.508(a)(2)<br>45 C.F.R. § 164.510<br>45 C.F.R. § 164.512<br>45 C.F.R. § 164.530(c)', '', 5, NULL),
(97, 'Use and Disclosure, Disaster Relief', 'PR-195', '45 C.F.R. §164.510(b)(4)', '', 5, NULL),
(98, 'Use and Disclosure, Specialized Government Functions', 'PR-200', '45 C.F.R. §164.512(k)(1) Military<br>45 C.F.R. §164.512(k)(2) National Security<br>45 C.F.R. §164.512(k)(3) Protective Services<br>45 C.F.R. §164.512(k)(4) Medical Suitability<br>45 C.F.R. §164.512(k)(5) Correctional<br>45 C.F.R. §164.512(k)(6) Public Benefits', '', 5, NULL),
(99, 'Use and Disclosure, Health Oversight', 'PR-205', '45 C.F.R. §164.512(d)', '', 5, NULL),
(100, 'Use and Disclosure, Health and Human Services - part of Health Oversight Reporting', 'PR-210', '45 C.F.R.§164.512(d)(iii)<br>45 C.F.R.§164.512(d)(iv)', '', 5, NULL),
(101, 'Use and Disclosure, Psychotherapy', 'PR-215', '45 C.F.R. §164.508(a)(2)<br>45 C.F.R. §164.508(b)(3)<br>45 C.F.R. §164.508(b)(4)', '', 5, NULL),
(102, 'Use and Disclosure for victims of abuse, neglect or domestic violence', 'PR-217', '45 C.F.R. §164.512(c )', '', 5, NULL),
(103, 'Use and Disclosure for Facility Directories', 'PR-219', '45 C.F.R. §164.510(a)(1) Opportunity to Object<br>45 C.F.R. §164.510(a)(3) In Emergencies', '', 5, NULL),
(104, 'Use and Disclosure, Public Health and Safety', 'PR-220', '45 C.F.R. §164.512(b)', '', 5, NULL),
(105, 'Use and Disclosure for Law Enforcement Purposes', 'PR-225', '45 C.F.R. §164.512(f)(1) Required by law<br>45 C.F.R. §164.512(f)(2) Identification<br>45 C.F.R. §164.512(f)(3) Victims of crime<br>45 C.F.R. §164.512(f)(4) Decedent of crime<br>45 C.F.R. §164.512(f)(5) Crime on premises<br>45 C.F.R. §164.512(f)(6) Reporting crime', '', 5, NULL),
(106, 'Use and Disclosure for Cadaveric organ, eye or tissue donation - part of After Patient Death', 'PR-230', '45 C.F.R. §164.512(h)', '', 5, NULL),
(107, 'Use and Disclosure for Workman''s Compensation - part of Judicial and Administrative', 'PR-235', '45 C.F.R. §164.512(l)', '', 5, NULL),
(108, 'Use and Disclosure for Emergency Treatment - part of Use of PHI', 'PR-240', '', '', 5, NULL),
(109, 'Use and Disclosure about Decedents, after Patient Death', 'PR-245', '45 C.F.R. §164.510(b)(5)<br>45 C.F.R. §164.512(g)', '', 5, NULL),
(110, 'Seperation of Employee Health Documents', 'PR-267', '45 CFR 160.103<br>45 CFR 164.512(b)(1)(v)', '', 5, NULL),
(111, 'Remote Access Policy', 'PS-143', '45 CFR §164.308(a)(3)(ii)(B)<br>45 CFR §164.308(a)(3)(ii)(C)<br>45 CFR §164.308(a)(4)(ii)(B)<br>45 CFR §164.308(a)(4)(ii)(C)<br>45 CFR §164.312(a)(2)(iii)', '', 2, NULL),
(112, 'Asset Inventory', 'PS-165a', '45 CFR §164.310(d)(2)(iii)', '', 2, NULL),
(113, 'Mechanism to Authenticate', 'TS-150', '45 C.F.R. §164.312(c)(2)', '', 3, NULL);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `regulations`
--
ALTER TABLE `regulations`
  ADD PRIMARY KEY (`rg_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `regulations`
--
ALTER TABLE `regulations`
  MODIFY `rg_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=114;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
