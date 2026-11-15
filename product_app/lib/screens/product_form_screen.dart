import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import '../models/category.dart';
import '../models/product.dart';
import '../services/api_service.dart';
import '../widgets/product_image.dart';

// Screen 2: Insert Product / Edit Product (same form for both)
class ProductFormScreen extends StatefulWidget {
  final Product? product; // null = Insert, not null = Edit

  const ProductFormScreen({super.key, this.product});

  @override
  State<ProductFormScreen> createState() => _ProductFormScreenState();
}

class _ProductFormScreenState extends State<ProductFormScreen> {
  final _formKey = GlobalKey<FormState>();

  final _nameController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _barcodeController = TextEditingController();
  final _expireDateController = TextEditingController();
  final _qtyController = TextEditingController(text: '0');
  final _priceInController = TextEditingController(text: '0.00');
  final _priceOutController = TextEditingController(text: '0.00');

  XFile? _pickedImage; // new picture chosen from the device
  Uint8List? _pickedBytes; // used to preview it

  List<Category> _categories = [];
  int? _selectedCategoryId;
  bool _saving = false;

  bool get _isEdit => widget.product != null;

  @override
  void initState() {
    super.initState();
    final p = widget.product;
    if (p != null) {
      _nameController.text = p.productName;
      _descriptionController.text = p.description ?? '';
      _barcodeController.text = p.barcode ?? '';
      _expireDateController.text = p.expireDate ?? '';
      _qtyController.text = p.qty.toString();
      _priceInController.text = p.unitPriceIn.toStringAsFixed(2);
      _priceOutController.text = p.unitPriceOut.toStringAsFixed(2);
      _selectedCategoryId = p.categoryId;
    }
    _loadCategories();
  }

  Future<void> _loadCategories() async {
    try {
      final categories = await ApiService.getCategories();
      setState(() => _categories = categories);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Cannot load categories: $e')));
    }
  }

  Future<void> _pickExpireDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: DateTime.tryParse(_expireDateController.text) ?? DateTime.now(),
      firstDate: DateTime(2000),
      lastDate: DateTime(2100),
    );
    if (picked != null) {
      _expireDateController.text = picked.toIso8601String().substring(0, 10); // yyyy-mm-dd
    }
  }

  // Choose a picture from the gallery / files / drive
  Future<void> _pickImage() async {
    final image = await ImagePicker().pickImage(
      source: ImageSource.gallery,
      maxWidth: 1200, // make big photos smaller (Laravel accepts max 2 MB)
      imageQuality: 85,
    );
    if (image == null) return;
    final bytes = await image.readAsBytes();
    setState(() {
      _pickedImage = image;
      _pickedBytes = bytes;
    });
  }

  Widget _buildImagePicker() {
    const size = 140.0;
    final preview = _pickedBytes != null
        ? ClipRRect(
            borderRadius: BorderRadius.circular(10),
            child: Image.memory(_pickedBytes!, width: size, height: size, fit: BoxFit.cover),
          )
        : ProductImage(url: widget.product?.imageUrl, size: size);

    return Center(
      child: Column(
        children: [
          GestureDetector(onTap: _pickImage, child: preview),
          const SizedBox(height: 8),
          OutlinedButton.icon(
            onPressed: _pickImage,
            icon: const Icon(Icons.photo_library_outlined),
            label: Text(_pickedBytes != null || widget.product?.imageUrl != null
                ? 'Change Image'
                : 'Choose Image'),
          ),
        ],
      ),
    );
  }

  String? _textOrNull(TextEditingController c) => c.text.trim().isEmpty ? null : c.text.trim();

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;

    final product = Product(
      productName: _nameController.text.trim(),
      description: _textOrNull(_descriptionController),
      categoryId: _selectedCategoryId!,
      barcode: _textOrNull(_barcodeController),
      expireDate: _textOrNull(_expireDateController),
      qty: int.parse(_qtyController.text),
      unitPriceIn: double.parse(_priceInController.text),
      unitPriceOut: double.parse(_priceOutController.text),
    );

    setState(() => _saving = true);
    try {
      if (_isEdit) {
        await ApiService.updateProduct(widget.product!.productId!, product, _pickedImage);
      } else {
        await ApiService.createProduct(product, _pickedImage);
      }
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(_isEdit ? 'Product updated successfully' : 'Product added successfully')),
      );
      Navigator.pop(context, true);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Save failed: $e')));
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  String? _required(String? value) =>
      (value == null || value.trim().isEmpty) ? 'This field is required' : null;

  String? _validInt(String? value) =>
      int.tryParse(value ?? '') == null ? 'Enter a whole number' : null;

  String? _validPrice(String? value) =>
      double.tryParse(value ?? '') == null ? 'Enter a valid price' : null;

  @override
  void dispose() {
    for (final c in [
      _nameController, _descriptionController, _barcodeController, _expireDateController,
      _qtyController, _priceInController, _priceOutController,
    ]) {
      c.dispose();
    }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    const gap = SizedBox(height: 12);

    return Scaffold(
      appBar: AppBar(
        title: Text(_isEdit ? 'Edit Product' : 'Insert Product'),
        backgroundColor: Colors.transparent,
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            _buildImagePicker(),
            const SizedBox(height: 20),
            TextFormField(
              controller: _nameController,
              decoration: const InputDecoration(labelText: 'Product Name *'),
              maxLength: 100,
              validator: _required,
            ),
            gap,
            DropdownButtonFormField<int>(
              // Only set the value once the categories list contains it
              initialValue: _categories.any((c) => c.categoryId == _selectedCategoryId)
                  ? _selectedCategoryId
                  : null,
              decoration: const InputDecoration(labelText: 'Category *'),
              items: _categories
                  .map((c) => DropdownMenuItem(value: c.categoryId, child: Text(c.categoryName)))
                  .toList(),
              onChanged: (value) => setState(() => _selectedCategoryId = value),
              validator: (value) => value == null ? 'Please choose a category' : null,
            ),
            gap,
            TextFormField(
              controller: _descriptionController,
              decoration: const InputDecoration(labelText: 'Description'),
              maxLength: 255,
              maxLines: 2,
            ),
            gap,
            TextFormField(
              controller: _barcodeController,
              decoration: const InputDecoration(labelText: 'Barcode'),
            ),
            gap,
            TextFormField(
              controller: _expireDateController,
              decoration: const InputDecoration(
                labelText: 'Expire Date',
                suffixIcon: Icon(Icons.calendar_today),
              ),
              readOnly: true,
              onTap: _pickExpireDate,
            ),
            gap,
            TextFormField(
              controller: _qtyController,
              decoration: const InputDecoration(labelText: 'Quantity *'),
              keyboardType: TextInputType.number,
              validator: _validInt,
            ),
            gap,
            Row(
              children: [
                Expanded(
                  child: TextFormField(
                    controller: _priceInController,
                    decoration: const InputDecoration(labelText: 'Unit Price In *'),
                    keyboardType: const TextInputType.numberWithOptions(decimal: true),
                    validator: _validPrice,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: TextFormField(
                    controller: _priceOutController,
                    decoration: const InputDecoration(labelText: 'Unit Price Out *'),
                    keyboardType: const TextInputType.numberWithOptions(decimal: true),
                    validator: _validPrice,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 20),
            SizedBox(
              height: 48,
              child: FilledButton.icon(
                onPressed: _saving ? null : _save,
                icon: _saving
                    ? const SizedBox(
                        width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2))
                    : const Icon(Icons.save),
                label: Text(_isEdit ? 'Update Product' : 'Save Product'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
