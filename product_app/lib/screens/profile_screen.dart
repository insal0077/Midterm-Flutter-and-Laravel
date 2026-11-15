import 'package:flutter/material.dart';

// ---- Change these to your own details ----
const String studentName = 'Your Name';
const String studentId = 'Your Student ID';
const String className = 'Your Class / Group';
const String subject = 'Mobile App Development';
const String lecturer = 'Lecturer Name';
// ------------------------------------------

// Profile page: student information and about this app
class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).colorScheme;

    return Scaffold(
      body: ListView(
        padding: EdgeInsets.zero,
        children: [
          // Header with avatar
          Container(
            padding: const EdgeInsets.fromLTRB(24, 64, 24, 32),
            decoration: BoxDecoration(
              gradient: LinearGradient(colors: [colors.primary, colors.tertiary]),
              borderRadius: const BorderRadius.vertical(bottom: Radius.circular(32)),
            ),
            child: Column(
              children: [
                CircleAvatar(
                  radius: 48,
                  backgroundColor: Colors.white,
                  child: Text(
                    studentName.isNotEmpty ? studentName[0].toUpperCase() : '?',
                    style: TextStyle(fontSize: 40, fontWeight: FontWeight.bold, color: colors.primary),
                  ),
                ),
                const SizedBox(height: 16),
                const Text(
                  studentName,
                  style: TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 4),
                Text(studentId, style: TextStyle(color: Colors.white.withValues(alpha: 0.9))),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                const Card(
                  child: Column(
                    children: [
                      _InfoTile(icon: Icons.badge_outlined, title: 'Student ID', value: studentId),
                      _InfoTile(icon: Icons.groups_outlined, title: 'Class', value: className),
                      _InfoTile(icon: Icons.menu_book_outlined, title: 'Subject', value: subject),
                      _InfoTile(icon: Icons.school_outlined, title: 'Lecturer', value: lecturer),
                    ],
                  ),
                ),
                const SizedBox(height: 12),
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('About this app',
                            style: Theme.of(context).textTheme.titleMedium!
                                .copyWith(fontWeight: FontWeight.bold)),
                        const SizedBox(height: 8),
                        const Text('Midterm assignment: Product CRUD using a Laravel REST API.'),
                        const SizedBox(height: 12),
                        const Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: [
                            Chip(avatar: Icon(Icons.flutter_dash, size: 18), label: Text('Flutter')),
                            Chip(avatar: Icon(Icons.api, size: 18), label: Text('Laravel 12 API')),
                            Chip(avatar: Icon(Icons.storage, size: 18), label: Text('MySQL')),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _InfoTile extends StatelessWidget {
  final IconData icon;
  final String title;
  final String value;

  const _InfoTile({required this.icon, required this.title, required this.value});

  @override
  Widget build(BuildContext context) {
    return ListTile(
      leading: Icon(icon, color: Theme.of(context).colorScheme.primary),
      title: Text(title, style: const TextStyle(fontSize: 13, color: Colors.grey)),
      subtitle: Text(value, style: const TextStyle(fontSize: 16, color: Colors.black87)),
    );
  }
}
