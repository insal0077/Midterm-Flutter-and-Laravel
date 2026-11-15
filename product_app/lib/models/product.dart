class Product {
  final int? productId;
  final String productName;
  final String? description;
  final int categoryId;
  final String? categoryName;
  final String? barcode;
  final String? expireDate;
  final int qty;
  final double unitPriceIn;
  final double unitPriceOut;
  final String? imageUrl; // full link to the uploaded picture (from Laravel)

  Product({
    this.productId,
    required this.productName,
    this.description,
    required this.categoryId,
    this.categoryName,
    this.barcode,
    this.expireDate,
    required this.qty,
    required this.unitPriceIn,
    required this.unitPriceOut,
    this.imageUrl,
  });

  // Laravel -> Dart
  factory Product.fromJson(Map<String, dynamic> json) {
    return Product(
      productId: json['ProductID'],
      productName: json['ProductName'],
      description: json['Description'],
      categoryId: int.parse(json['CategoryID'].toString()),
      categoryName: json['category'] != null ? json['category']['category_name'] : null,
      barcode: json['Barcode'],
      expireDate: json['Expiredate'],
      qty: int.parse(json['Qty'].toString()),
      unitPriceIn: double.parse(json['UnitPriceIn'].toString()),
      unitPriceOut: double.parse(json['UnitPriceOut'].toString()),
      imageUrl: json['image_url'],
    );
  }

  // Dart -> Laravel (form fields; empty text is saved as NULL by Laravel)
  Map<String, String> toFields() {
    return {
      'ProductName': productName,
      'Description': description ?? '',
      'CategoryID': categoryId.toString(),
      'Barcode': barcode ?? '',
      'Expiredate': expireDate ?? '',
      'Qty': qty.toString(),
      'UnitPriceIn': unitPriceIn.toString(),
      'UnitPriceOut': unitPriceOut.toString(),
    };
  }
}
