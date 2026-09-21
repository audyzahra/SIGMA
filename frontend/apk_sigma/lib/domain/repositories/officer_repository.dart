import '../entities/officer_entities.dart';

abstract class OfficerRepository {
  Future<List<OfficerTask>> getTasks();
  Future<List<FieldReport>> getReports();
  Future<void> updateTaskStatus(String taskId, TaskStatus status);
  Future<void> createReport(FieldReport report);
  Future<void> sync();
}
