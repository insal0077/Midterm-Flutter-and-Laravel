import 'package:flutter/material.dart';

// Square product picture with a placeholder when there is no image.
class ProductImage extends StatelessWidget {
  final String? url;
  final double size;

  const ProductImage({super.key, this.url, this.size = 56});

  @override
  Widget build(BuildContext context) {
    final placeholder = Container(
      width: size,
      height: size,
      color: Theme.of(context).colorScheme.primaryContainer,
      child: Icon(Icons.inventory_2_outlined,
          size: size * 0.5, color: Theme.of(context).colorScheme.primary),
    );

    return ClipRRect(
      borderRadius: BorderRadius.circular(10),
      child: url == null
          ? placeholder
          : Image.network(
              url!,
              width: size,
              height: size,
              fit: BoxFit.cover,
              // On Chrome, show the picture with a normal <img> tag if needed
              webHtmlElementStrategy: WebHtmlElementStrategy.fallback,
              errorBuilder: (context, error, stack) => placeholder,
            ),
    );
  }
}
