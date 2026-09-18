class CitizenProfile {
  const CitizenProfile({required this.name, required this.email, required this.location});
  final String name;
  final String email;
  final String location;
}

class CitizenReport {
  const CitizenReport({required this.id, required this.title, required this.status, required this.time, required this.location, required this.progress});
  final String id;
  final String title;
  final String status;
  final String time;
  final String location;
  final int progress;
}
