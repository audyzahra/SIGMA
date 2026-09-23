import '../entities/officer_entities.dart';

abstract class OfficerRepository {
  Future<List<OfficerTask>> getTasks();
  Future<List<FieldReport>> getReports();
  Future<OfficerProfile> getProfile();
  Future<void> createReport(String taskId, String description);
  Future<void> updateTaskStatus(String taskId, TaskStatus status);
  Future<void> acceptTask(String taskId);
}
