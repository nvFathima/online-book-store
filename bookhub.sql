-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 15, 2024 at 07:40 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bookhub`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetTotalCopiesBySeller` (IN `sellerId` INT, OUT `totalCopies` INT)   BEGIN
    SELECT SUM(No_of_copies) INTO totalCopies
    FROM books
    WHERE seller_id = sellerId;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `postcode` varchar(20) NOT NULL,
  `state` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`address_id`, `user_id`, `address_line1`, `address_line2`, `postcode`, `state`, `is_active`) VALUES
(4, 14, 'Es House', 'Town Hall Road', '', 'nowhere', 1),
(5, 12, 'Aleena House', 'Prayag Road', '', 'Kerala', 1);

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `user_name` varchar(30) DEFAULT NULL,
  `password` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `user_name`, `password`) VALUES
(101, 'fathimanv', 'fa#123');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `title_id` int(11) NOT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `author` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `original_price` float DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `condition_id` int(11) DEFAULT NULL,
  `unit_price` float DEFAULT NULL,
  `total_price` float DEFAULT NULL,
  `No_of_copies` int(11) DEFAULT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `added_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`title_id`, `seller_id`, `title`, `author`, `description`, `original_price`, `category_id`, `condition_id`, `unit_price`, `total_price`, `No_of_copies`, `status`, `added_at`) VALUES
(1, 12, 'Harry Potter and the Philosopher\'s Stone', 'JK Rowling', 'The book is about 11 year old Harry Potter, who receives a letter saying that he is invited to attend Hogwarts, school of witchcraft and wizardry. He then learns that a powerful wizard and his minions are after the sorcerer\'s stone that will make this evil wizard immortal and undefeatable.', 380, 214, 301, 380, 2280, 6, 'accepted', '2024-10-02 04:51:58'),
(2, 12, 'Harry Potter and the Chamber of Secrets', 'JK Rowling', 'Harry Potter and the Chamber of Secrets is a fantasy novel written by British author J. K. Rowling and the second novel in the Harry Potter series. The plot follows Harry\'s second year at Hogwarts School of Witchcraft and Wizardry, during which a series of messages on the walls of the school\'s corridors warn that the \"Chamber of Secrets\" has been opened and that the \"heir of Slytherin\" would kill all pupils who do not come from all-magical families.', 400, 214, 301, 400, 2000, 1, 'accepted', '2024-10-15 16:38:45'),
(3, 12, 'Harry Potter and the Prisoner of Azkaban', 'JK Rowling', 'Harry Potter and the Prisoner of Azkaban is the third instalment in the Harry Potter series. The novel follows Harry Potter, a young wizard, in his third year at Hogwarts School of Witchcraft and Wizardry. Along with friends Ron Weasley and Hermione Granger, Harry investigates Sirius Black, an escaped prisoner from Azkaban, the wizard prison, believed to be one of Lord Voldemort\'s old allies.', 440, 214, 302, 396, 792, 0, 'accepted', '2024-10-15 12:33:10'),
(4, 12, 'My Autobiography', 'Charlie Chaplin', 'My Autobiography is a book by Charlie Chaplin, first published by Simon & Schuster in 1964. Along with Chaplin: His Life and Art (1985), it provided the source material for the 1992 feature film Chaplin. It provides a revealing look into the life of a 20th-century filmmaker and celebrity.', 520, 201, 303, 390, 520, 1, 'accepted', '2024-10-02 04:31:12'),
(6, 14, 'Organizational Behavior', 'Stephen P. Robbins', 'The text, Organizational Behavior provides a comprehensive overview of several topics, including: motivation, communication, managing groups and teams, conflict resolution, power and politics, making decisions, etc.', 1200, 210, 303, 900, 1200, 1, 'accepted', '2024-10-02 04:43:41'),
(7, 12, 'PHP and MySQL Web Development', 'Luke Welling, Laura Thomson', '\"PHP and MySQL Web Development\" teaches the reader to develop dynamic, secure, commercial Web sites. Using the same accessible, popular teaching style of the first edition, this best-selling book has been updated to reflect the rapidly changing landscape of MySQL and PHP.The book teaches the reader to integrate and implement these technologies by following real-world examples and working sample projects, and also covers related technologies needed to build a commercial Web site, such as SSL, shopping carts, and payment systems.The second edition includes new coverage of how to work with XML in developing a PHP and MySQL site, and how to draw on the valuable resources of the PEAR repository of code and extensions.', 900, 210, 303, 675, 900, 0, 'accepted', '2024-10-15 16:38:45'),
(9, 14, 'Ants Among Elephants', 'Sujata Gidla', 'In Ants Among Elephants, Sujata Gidla, born in the untouchable community traces the life of three generations of her family. The book shakes up the idea of freedom in newly Independent India. The memoir focuses on the life of Gidla’s uncle KG Satyamurthy who was majorly involved in the communist struggle in Telangana. Gidla also narrates the extensive financial and physical hurdles her mother had to overcome owing to her caste and family background. I loved this memoir because it checks your privilege and reveals how caste operates in disguise to maintain the status quo.', 480, 208, 301, 480, 2400, 5, 'accepted', '2024-10-14 09:57:25'),
(10, 14, 'The Body: A Guide for Occupants', 'Bill Bryson', 'In the bestselling, prize-winning A Short History of Nearly Everything, Bill Bryson achieved the seemingly impossible by making the science of our world both understandable and entertaining to millions of people around the globe.\r\n\r\nNow he turns his attention inwards to explore the human body, how it functions and its remarkable ability to heal itself. Full of extraordinary facts and astonishing stories, The Body: A Guide for Occupants is a brilliant, often very funny attempt to understand the miracle of our physical and neurological make up.', 342, 211, 301, 342, 2736, 5, 'accepted', '2024-10-15 16:25:10');

-- --------------------------------------------------------

--
-- Table structure for table `book_images`
--

CREATE TABLE `book_images` (
  `image_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_images`
--

INSERT INTO `book_images` (`image_id`, `book_id`, `image_path`) VALUES
(1, 1, '../uploads/images/66fcca50edede.jpeg'),
(2, 2, '../uploads/images/66fccaefcfe1c.jpeg'),
(4, 4, '../uploads/images/66fccc3c8d54c.jpeg'),
(5, 4, '../uploads/images/66fccc3c8e84b.jpeg'),
(7, 6, '../uploads/images/66fccf512ed80.jpeg'),
(13, 9, '../uploads/images/670cea071bc46.jpeg'),
(14, 9, '../uploads/images/670cea071e14e.jpg'),
(15, 10, '../uploads/images/670ceaa602f9a.jpeg'),
(16, 10, '../uploads/images/670ceaa603c6a.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(201, 'Autobiography'),
(202, 'Biography'),
(203, 'Business/economics'),
(204, 'Guide'),
(205, 'History'),
(206, 'Humor'),
(207, 'Journal'),
(208, 'Memoir'),
(209, 'Philosophy'),
(210, 'Textbook'),
(211, 'Science'),
(212, 'Children\'s'),
(213, 'Classic'),
(214, 'Fantasy'),
(215, 'Historical fiction');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `post_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`comment_id`, `post_id`, `user_id`, `content`, `created_at`) VALUES
(1, 1, 13, 'Heyy', '2024-10-02 02:30:35'),
(2, 3, 13, 'Beautiful Work!', '2024-10-02 03:31:57'),
(3, 4, 13, 'How is this guys?', '2024-10-02 03:32:30'),
(4, 3, 10, 'Wooww!!👌', '2024-10-12 16:20:39');

-- --------------------------------------------------------

--
-- Table structure for table `conditions`
--

CREATE TABLE `conditions` (
  `condition_id` int(11) NOT NULL,
  `condition_name` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conditions`
--

INSERT INTO `conditions` (`condition_id`, `condition_name`) VALUES
(301, 'new'),
(302, 'Almost-new'),
(303, 'Very-good'),
(304, 'Good');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `Donation_id` int(11) NOT NULL,
  `Donor_id` int(11) NOT NULL,
  `Book_title` varchar(255) NOT NULL,
  `Author` varchar(100) NOT NULL,
  `No_of_copies` int(11) NOT NULL,
  `Category_id` int(11) NOT NULL,
  `condition_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `requested_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`Donation_id`, `Donor_id`, `Book_title`, `Author`, `No_of_copies`, `Category_id`, `condition_id`, `status`, `requested_time`) VALUES
(2, 12, 'NCERT', 'Kamala Das', 3, 210, 301, 'Accepted', '2024-10-14 11:59:25'),
(3, 10, 'Little Singam', 'Njan thanne', 1, 212, 303, 'Rejected', '2024-10-14 14:30:42');

-- --------------------------------------------------------

--
-- Table structure for table `donation_images`
--

CREATE TABLE `donation_images` (
  `image_id` int(11) NOT NULL,
  `donation_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donation_images`
--

INSERT INTO `donation_images` (`image_id`, `donation_id`, `image_path`) VALUES
(1, 2, '../uploads/donations/670d079d9ce9b.jpg'),
(2, 3, '../uploads/donations/670d2b120b5ed.png');

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--

CREATE TABLE `email_verifications` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `verification_code` varchar(6) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_verifications`
--

INSERT INTO `email_verifications` (`id`, `email`, `verification_code`, `created_at`) VALUES
(1, 'nvfathima2206@gmail.com', '359514', '2024-10-02 01:48:12'),
(2, 'fathima.22ubc130@mariancollege.org', '348481', '2024-10-02 01:56:04');

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `like_id` int(11) NOT NULL,
  `post_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`like_id`, `post_id`, `user_id`, `created_at`) VALUES
(10, 1, 13, '2024-10-02 02:30:51'),
(11, 3, 13, '2024-10-02 03:15:39'),
(12, 5, 13, '2024-10-02 03:15:54'),
(15, 5, 10, '2024-10-15 07:34:21'),
(16, 3, 10, '2024-10-15 08:14:40'),
(17, 8, 10, '2024-10-15 08:57:08'),
(19, 1, 10, '2024-10-15 09:06:43'),
(20, 2, 10, '2024-10-15 09:07:12');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `shipping_address_id` int(11) NOT NULL,
  `order_status` varchar(20) NOT NULL DEFAULT 'Pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expected_delivery` date DEFAULT NULL,
  `received_time` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `total_amount`, `shipping_address_id`, `order_status`, `payment_method`, `created_at`, `updated_at`, `expected_delivery`, `received_time`) VALUES
(7, 10, 396.00, 2, 'Received', 'on', '2024-10-15 12:18:32', '2024-10-15 14:51:26', '2024-10-31', NULL),
(9, 10, 342.00, 2, 'Accepted', 'on', '2024-10-15 12:28:12', '2024-10-15 16:25:10', '2024-10-19', NULL),
(10, 10, 342.00, 3, 'Paid', 'on', '2024-10-15 15:08:08', '2024-10-15 15:08:35', NULL, NULL),
(11, 13, 1075.00, 4, '', 'on', '2024-10-15 16:36:31', '2024-10-15 16:49:21', '2024-10-18', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) GENERATED ALWAYS AS (`quantity` * `unit_price`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `book_id`, `quantity`, `unit_price`) VALUES
(1, 7, 3, 1, 396.00),
(2, 9, 10, 1, 342.00),
(3, 10, 10, 1, 342.00),
(4, 11, 2, 1, 400.00),
(5, 11, 7, 1, 675.00);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset`
--

CREATE TABLE `password_reset` (
  `email` varchar(255) NOT NULL,
  `verification_code` varchar(6) NOT NULL,
  `expire_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`post_id`, `user_id`, `title`, `content`, `created_at`, `image_path`) VALUES
(1, 10, 'First Post', 'Hey all, This is a testing post.', '2024-10-02 00:55:48', NULL),
(2, 10, 'Adding one more', 'Testing like functionality', '2024-10-02 01:06:23', NULL),
(3, 13, 'What is an anecdote?', 'An anecdote is a short story — usually about a very specific subject matter — that’s told in order to illuminate a greater point regarding a situational, narrative or thematic principle. We tell each other these stories all the time in everyday life, usually humorous in nature. In storytelling, they can aid in characterization of the teller and/or their subject. Some movies like Big Fish quite literally use these \"fish stories\" as the crux of the conflict. Will Bloom tries to understand if the stories his father told him about his life were real or fantasy.', '2024-10-02 02:30:08', NULL),
(4, 13, 'Anecdote Examples in Everyday Life', 'Oh, I love Ireland! I visited the west coast six times last year. Last time I went to Kilmacduagh, an old monastery where the winds whip with songs of the deceased who are laid to rest there. While I was there, I swore I heard something. I think it was a ghost!', '2024-10-02 03:14:42', NULL),
(5, 13, 'El Mason', 'El Meson is my favorite Mexican restaurant. They have the best Sunday brunch every week. One time when I went there, they prepared a wonderful traditional buffet with tetelas, gordita de harina, café de olla in a clay pot, and more that you just can’t get anywhere else. It was just like my abuela used to make!', '2024-10-02 03:15:25', NULL),
(8, 10, 'Testing purpose', 'blahhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh vvvvvvvvvvvvvvvvvvvvvvvvvvvvvv rrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrr....!!!!!!!!!!!!!!!!!!', '2024-10-15 08:56:59', 'uploads/670e2e5bd9cea_18BOOKGIDLA2-superJumbo-v3.jpg'),
(9, 10, 'Meaningless', 'chumma oru content..veendum image uplaod test cheyyan', '2024-10-15 09:04:43', '../uploads/670e302bd1686_images (1).jpeg'),
(10, 10, 'new one', 'image illathe nokkatte', '2024-10-15 09:05:45', NULL),
(11, 10, 'When will pagination control work', 'testing the page limit', '2024-10-15 09:06:25', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `rejection_messages`
--

CREATE TABLE `rejection_messages` (
  `message_id` int(11) NOT NULL,
  `book_title` varchar(255) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `rejection_reason` text NOT NULL,
  `rejected_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rejection_messages`
--

INSERT INTO `rejection_messages` (`message_id`, `book_title`, `seller_id`, `rejection_reason`, `rejected_at`, `is_read`) VALUES
(1, 'Quantitative aptitude for competitive examinations', 14, 'Something Wrong!', '2024-10-13 07:04:11', 1),
(2, 'India\'s Struggle for Independence Book', 14, 'Violated Policies', '2024-10-14 10:39:25', 1);

-- --------------------------------------------------------

--
-- Table structure for table `shipping_addresses`
--

CREATE TABLE `shipping_addresses` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address_line_1` varchar(255) NOT NULL,
  `address_line_2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipping_addresses`
--

INSERT INTO `shipping_addresses` (`address_id`, `user_id`, `address_line_1`, `address_line_2`, `city`, `state`, `postal_code`, `country`, `created_at`) VALUES
(1, 10, 'Es House', 'Town Hall Road', 'Parappanangadi', 'Kerala', '676303', 'India', '2024-10-02 10:22:39'),
(2, 10, 'Ameena Manzil', 'Tirurangadi', 'Tirurangadi', 'Kerala', '676306', 'India', '2024-10-14 12:08:57'),
(3, 10, 'Riya Mahal', 'Cherumukku', 'Tirurangadi', 'Kerala', '676306', 'India', '2024-10-15 12:58:21'),
(4, 13, 'Anugraham', 'Nila lane', 'Kumily', 'Kerala', '234161', 'India', '2024-10-15 16:36:06');

--
-- Triggers `shipping_addresses`
--
DELIMITER $$
CREATE TRIGGER `check_address_limit` BEFORE INSERT ON `shipping_addresses` FOR EACH ROW BEGIN
    DECLARE address_count INT;
    SELECT COUNT(*) INTO address_count
    FROM shipping_addresses
    WHERE user_id = NEW.user_id;
    
    IF address_count >= 3 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cannot add more than 3 addresses per user';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(30) NOT NULL,
  `last_name` varchar(30) NOT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('user','seller') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_active` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `first_name`, `last_name`, `contact_no`, `email`, `password`, `user_type`, `created_at`, `last_active`) VALUES
(10, 'Azrah', 'N V', '9995924007', 'fathimanv2004@gmail.com', '$2y$10$TaaFTAQ8tfH60ChZ3.ruM.bNJqO4eCTo1xNllpFsd8jzBZVtNbby6', 'user', '2024-09-18 15:31:19', '2024-10-15 17:18:44'),
(12, 'Fathima', 'N V', '8281563726', 'fathimanv627@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$QjNkdTdNVi9nOE50eWFaYw$MSLSpDdyU9jMzRtyqzekrgjfjGGI19b+8sXAOiWoWqg', 'seller', '2024-10-01 07:27:42', '2024-10-15 16:38:11'),
(13, 'Sheethal', 'Kochery', '9931587990', 'nvfathima2206@gmail.com', '$2y$10$.aChwukpU3WbWa9dEbj50e1RTU3CGlL6q0UwqWB8SZ0Pme.WVSeTS', 'user', '2024-10-02 01:49:06', '2024-10-15 16:52:47'),
(14, 'Sahal', 'Muhammed', '7052617181', 'fathima.22ubc130@mariancollege.org', '$argon2id$v=19$m=65536,t=4,p=1$Rzl0TlNDU21peGVVdXNlMQ$9exqH/CcEMVXyyOcQgSHIhkgkS+YOnk2IqEIPrC2fbw', 'seller', '2024-10-02 01:56:24', '2024-10-15 16:39:23');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `addresses_ibfk_1` (`user_id`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`title_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `condition_id` (`condition_id`),
  ADD KEY `books_ibfk_1` (`seller_id`);

--
-- Indexes for table `book_images`
--
ALTER TABLE `book_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `cart_ibfk_2` (`book_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `conditions`
--
ALTER TABLE `conditions`
  ADD PRIMARY KEY (`condition_id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`Donation_id`),
  ADD KEY `Donor_id` (`Donor_id`),
  ADD KEY `Category_id` (`Category_id`),
  ADD KEY `donations_ibfk_3` (`condition_id`);

--
-- Indexes for table `donation_images`
--
ALTER TABLE `donation_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `donation_id` (`donation_id`);

--
-- Indexes for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`like_id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `shipping_address_id` (`shipping_address_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `password_reset`
--
ALTER TABLE `password_reset`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `rejection_messages`
--
ALTER TABLE `rejection_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `fk_user_id` (`user_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `wishlist_ibfk_2` (`book_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `title_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `book_images`
--
ALTER TABLE `book_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=217;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `conditions`
--
ALTER TABLE `conditions`
  MODIFY `condition_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=305;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `Donation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `donation_images`
--
ALTER TABLE `donation_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `like_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `rejection_messages`
--
ALTER TABLE `rejection_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `books_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `books_ibfk_3` FOREIGN KEY (`condition_id`) REFERENCES `conditions` (`condition_id`);

--
-- Constraints for table `book_images`
--
ALTER TABLE `book_images`
  ADD CONSTRAINT `book_images_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`title_id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`title_id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`Donor_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `donations_ibfk_2` FOREIGN KEY (`Category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `donations_ibfk_3` FOREIGN KEY (`condition_id`) REFERENCES `conditions` (`condition_id`);

--
-- Constraints for table `donation_images`
--
ALTER TABLE `donation_images`
  ADD CONSTRAINT `donation_images_ibfk_1` FOREIGN KEY (`donation_id`) REFERENCES `donations` (`Donation_id`) ON DELETE CASCADE;

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `orders_ibfk_4` FOREIGN KEY (`shipping_address_id`) REFERENCES `shipping_addresses` (`address_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`title_id`);

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `rejection_messages`
--
ALTER TABLE `rejection_messages`
  ADD CONSTRAINT `rejection_messages_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `shipping_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`title_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
