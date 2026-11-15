import 'dart:convert';

import 'package:flutter/foundation.dart' show kIsWeb, defaultTargetPlatform, TargetPlatform;
import 'package:http/http.dart' as http;
import 'package:image_picker/image_picker.dart';

import '../models/category.dart';
import '../models/product.dart';

class ApiService {
  // Address of the Laravel server (php artisan serve --host=0.0.0.0 --port=8000)
  // - Android emulator uses 10.0.2.2 to reach the computer.
  // - Chrome / Windows use 127.0.0.1.
  // - A real phone: replace with your computer's IP, e.g. http://192.168.1.10:8000/api
  static String get baseUrl {
    if (!kIsWeb && defaultTargetPlatform == TargetPlatform.android) {
      return 'http://10.0.2.2:8000/api';
    }
    return 'http://127.0.0.1:8000/api';
  }

  static const Map<String, String> _headers = {'Accept': 'application/json'};

  // Display Products (GET /api/products)
  static Future<List<Product>> getProducts() async {
    final response = await http.get(Uri.parse('$baseUrl/products'), headers: _headers);
    if (response.statusCode == 200) {
      final List data = jsonDecode(response.body);
      return data.map((item) => Product.fromJson(item)).toList();
    }
    throw Exception(_errorMessage(response));
  }

  // Categories for the dropdown in the form (GET /api/categories)
  static Future<List<Category>> getCategories() async {
    final response = await http.get(Uri.parse('$baseUrl/categories'), headers: _headers);
    if (response.statusCode == 200) {
      final List data = jsonDecode(response.body);
      return data.map((item) => Category.fromJson(item)).toList();
    }
    throw Exception(_errorMessage(response));
  }

  // Insert Product (POST /api/products) - form-data so a picture can be uploaded
  static Future<void> createProduct(Product product, XFile? image) async {
    final response = await _sendForm('$baseUrl/products', product, image);
    if (response.statusCode != 201) {
      throw Exception(_errorMessage(response));
    }
  }

  // Edit Product (PUT /api/products/{id})
  // Files can only be sent with POST, so we add _method=PUT and Laravel treats it as PUT.
  static Future<void> updateProduct(int id, Product product, XFile? image) async {
    final response = await _sendForm('$baseUrl/products/$id', product, image, method: 'PUT');
    if (response.statusCode != 200) {
      throw Exception(_errorMessage(response));
    }
  }

  // Delete Product (DELETE /api/products/{id})
  static Future<void> deleteProduct(int id) async {
    final response = await http.delete(Uri.parse('$baseUrl/products/$id'), headers: _headers);
    if (response.statusCode != 200) {
      throw Exception(_errorMessage(response));
    }
  }

  static Future<http.Response> _sendForm(String url, Product product, XFile? image,
      {String? method}) async {
    final request = http.MultipartRequest('POST', Uri.parse(url));
    request.headers.addAll(_headers);
    request.fields.addAll(product.toFields());
    if (method != null) request.fields['_method'] = method;

    if (image != null) {
      // readAsBytes works on Android, Windows and Chrome
      request.files.add(http.MultipartFile.fromBytes(
        'ProductImage',
        await image.readAsBytes(),
        filename: image.name,
      ));
    }

    final streamed = await request.send();
    return http.Response.fromStream(streamed);
  }

  static String _errorMessage(http.Response response) {
    try {
      return jsonDecode(response.body)['message'] ?? 'Error ${response.statusCode}';
    } catch (_) {
      return 'Error ${response.statusCode}';
    }
  }
}
