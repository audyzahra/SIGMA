import 'package:flutter/material.dart';

import '../domain/entities/officer_entities.dart';
import '../widgets/officer_widgets.dart';

String statusLabel(TaskStatus status) {
  return switch (status) {
    TaskStatus.assigned => 'Ditugaskan',
    TaskStatus.accepted => 'Diterima',
    TaskStatus.enRoute => 'Dalam perjalanan',
    TaskStatus.arrived => 'Tiba di lokasi',
    TaskStatus.handling => 'Penanganan',
    TaskStatus.extinguishing => 'Api padam',
    TaskStatus.cooling => 'Pendinginan',
    TaskStatus.completed => 'Selesai',
    TaskStatus.cancelled => 'Dibatalkan',
  };
}

TaskStatus? nextStatus(TaskStatus status) {
  return switch (status) {
    TaskStatus.assigned => TaskStatus.accepted,
    TaskStatus.accepted => TaskStatus.enRoute,
    TaskStatus.enRoute => TaskStatus.arrived,
    TaskStatus.arrived => TaskStatus.handling,
    TaskStatus.handling => TaskStatus.completed,
    _ => null,
  };
}

String actionLabel(TaskStatus status) {
  return statusLabel(status);
}

Color statusColor(TaskStatus status) {
  return switch (status) {
    TaskStatus.completed => sigmaGreen,
    TaskStatus.handling => sigmaRed,
    TaskStatus.arrived => sigmaBlue,
    TaskStatus.enRoute => sigmaOrange,
    TaskStatus.accepted => sigmaBlue,
    _ => sigmaOrange,
  };
}