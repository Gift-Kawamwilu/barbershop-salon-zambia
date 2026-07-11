<?php
// export_services_xml.php - Generates an XML feed of the services catalogue
require_once __DIR__ . '/connect.php';

header('Content-Type: application/xml; charset=utf-8');

try {
    $stmt = $con->query("SELECT service_id, service_name, service_description, service_price, duration_minutes, category_id FROM services WHERE is_active = 1 OR is_active IS NULL ORDER BY service_name");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    header('Content-Type: text/plain');
    die('Failed to fetch services: ' . $e->getMessage());
}

// Build XML using DOMDocument for safe escaping
$dom = new DOMDocument('1.0', 'UTF-8');
$dom->formatOutput = true;

$root = $dom->createElement('services');
$root->setAttribute('generated', date('c'));
$dom->appendChild($root);

foreach ($services as $service) {
    $serviceNode = $dom->createElement('service');
    $serviceNode->setAttribute('id', $service['service_id']);

    $name = $dom->createElement('name');
    $name->appendChild($dom->createTextNode($service['service_name'] ?? ''));
    $serviceNode->appendChild($name);

    $description = $dom->createElement('description');
    $description->appendChild($dom->createTextNode($service['service_description'] ?? ''));
    $serviceNode->appendChild($description);

    $price = $dom->createElement('price');
    $price->appendChild($dom->createTextNode(number_format((float)($service['service_price'] ?? 0), 2)));
    $serviceNode->appendChild($price);

    $duration = $dom->createElement('duration_minutes');
    $duration->appendChild($dom->createTextNode($service['duration_minutes'] ?? ''));
    $serviceNode->appendChild($duration);

    $categoryId = $dom->createElement('category_id');
    $categoryId->appendChild($dom->createTextNode($service['category_id'] ?? ''));
    $serviceNode->appendChild($categoryId);

    $root->appendChild($serviceNode);
}

echo $dom->saveXML();
